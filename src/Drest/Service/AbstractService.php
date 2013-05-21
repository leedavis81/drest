<?php
namespace Drest\Service;


use Doctrine\ORM\EntityManager,
	Drest\DrestException,
	Drest\Representation,
	Drest\Request,
	Drest\Manager,
	Drest\Query\ResultSet,
	Drest\Mapping\RouteMetaData,
	Drest\ErrorHandler\AbstractHandler;

class AbstractService
{

    /**
     * Doctrine Entity Manager
     * @var Doctrine\ORM\EntityManager $em
     */
    protected $em;

    /**
     * Drest Manager
     * @var Drest\Manager $dm
     */
    protected $dm;

	/**
	 * Drest request object
	 * @var Drest\Request $request
	 */
	protected $request;

	/**
	 * Drest response object
	 * @var Drest\Response $response
	 */
	protected $response;

	/**
	 * When a route object is matched, it's injected into the service class
	 * @var Drest\Mapping\RouteMetaData $route
	 */
	protected $matched_route;

	/**
	 * A representation instance determined either from configuration or client media accept type - can be null if none matched
	 * @var Drest\Representation\AbstractRepresentation $representation
	 */
	protected $representation;

	/**
	 * The error handler instance
	 * @var Drest\ErrorHandler\AbstractHandler $error_handler
	 */
	protected $error_handler;

    /**
     * addional key fields that are included in partial queries to make the DQL valid
     * These columns should be purged from the result set
     * @var array $addedKeyFields
     */
    protected $addedKeyFields;


    /**
     * Initialise a new instance of a Drest service
     * @param \Doctrine\ORM\EntityManager $em The EntityManager to use.
     * @param \Drest\Manager $dm The Drest Manager object
     */
    public function __construct(EntityManager $em, Manager $dm)
    {
        $this->em = $em;
        $this->dm = $dm;

        $this->request = $dm->getRequest();
        $this->response = $dm->getResponse();
    }

    /**
     * Called on sucessful routing of a service call
     * Prepares the service to a request to be rendered
     * @todo: Fires off any events registered to preLoad
     * @return boolean $result - if false then fail fast no call to runCallMethod() should be made.
     */
    public function setupRequest()
    {
        // Make sure we have a route matched
        //@todo: this shouldn't throw an exception, pass failure to the error handler
    	if (!$this->matched_route instanceof RouteMetaData)
	    {
            DrestException::noMatchedRouteSet();
	    }

        // If its a GET request and no expose fields are present, fail early
	    $expose = $this->matched_route->getExpose();
        if ($this->request->getHttpMethod() == Request::METHOD_GET && (empty($expose) || (sizeof($expose) == 1 && empty($expose[0]))))
        {
            $this->renderDeterminedRepresentation($this->createResultSet(array()));
            return false;
        }


	    // @todo: Run verb type setup operations (do we want to break down by verb? or entity name? - evm)
	    switch ($this->request->getHttpMethod())
	    {
	        case Request::METHOD_GET:
	            break;
	    }

	    return true;
    }

    /**
     * Run the call method required on this service object
     */
    final public function runCallMethod()
    {
        // Use a default call if the DefaultService class is being used (allow for extension)
        $callMethod = (get_class($this) === 'Drest\Service\DefaultService') ? $this->getDefaultMethod() : $this->matched_route->getServiceCallMethod();
        if (!method_exists($this, $callMethod))
        {
            throw DrestException::unknownServiceMethod(get_class($this), $callMethod);
        }
        $this->$callMethod();
    }

	/**
	 * Inspects the request object and returns the default service method based on the entity type and verb used
	 * Eg. a GET request to a single element will return getElement()
	 * 	   a GET request to a collection element will return getCollection()
	 * 	   a POST request to a single element will return postElement()
	 * @return string $methodName
	 */
	final public function getDefaultMethod()
	{
	    $functionName = '';
	    $httpMethod = $this->request->getHttpMethod();
	    switch ($httpMethod)
	    {
	        case Request::METHOD_OPTIONS:
            case Request::METHOD_TRACE:
                $functionName = strtolower($httpMethod) . 'Request';
	            break;
            case Request::METHOD_CONNECT:
            case Request::METHOD_PATCH:
            case Request::METHOD_PROPFIND:
            case Request::METHOD_HEAD:
                //@todo: support implementation for these
                break;
            default:
                $functionName = strtolower($httpMethod);
                $functionName .= ($this->matched_route->isCollection()) ? 'Collection' : 'Element';
                break;
	    }
	    return $functionName;
	}


	/**
	 * Set the matched route object
	 * @param Drest\Mapping\RouteMetaData $matched_route
	 */
	public function setMatchedRoute(RouteMetaData $matched_route)
	{
        $this->matched_route = $matched_route;
	}

	/**
	 * Get the route object that was matched
	 * @return Drest\Mapping\RouteMetaData $matched_route
	 */
	public function getMatchedRoute()
	{
	    return $this->matched_route;
	}

	/**
	 * Set any predetermined representation instance
	 * @param Representation\AbstractRepresentation $representation
	 */
	public function setRepresentation(Representation\AbstractRepresentation $representation)
	{
	    $this->representation = $representation;
	}

	/**
	 * Get the predetermined representation
	 * @param Representation\AbstractRepresentation $representation
	 */
	public function getRepresentation()
	{
	    return $this->representation;
	}

	/**
	 * Set the error handler object
	 * @param Drest\ErrorHandler\AbstractHandler $error_handler
	 */
	public function setErrorHandler(AbstractHandler $error_handler)
	{
	    $this->error_handler = $error_handler;
	}

	/**
	 * Handle an error
	 * @param \Exception $e
  	 * @param $defaultResponseCode the default response code to use if no match on exception type occurs
  	 * @return ResultSet the error result set
	 */
	public function handleError(\Exception $e, $defaultResponseCode = 500)
	{
	    $this->error_handler->error($e, $defaultResponseCode);
	    $this->response->setStatusCode($this->error_handler->getReponseCode());

        return $this->error_handler->getResultSet();
	}

    /**
     * A recursive function to process the specified expose fields
     * @param array $fields - expose fields to process
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param \Doctrine\ORM\Mapping\ClassMetadata $classMetaData
	 * @param string $key - The key of the expose entry being processed
     * @param array $addedKeyFields
     */
	protected function registerExpose($fields, \Doctrine\ORM\QueryBuilder $qb, \Doctrine\ORM\Mapping\ClassMetadata $classMetaData, &$addedKeyFields = array(), $key = null)
	{
	    if (empty($fields))
	    {
	        return $qb;
	    }

	    $addedKeyFields = (array) $addedKeyFields;
	    $classAlias = $this->getAlias($classMetaData->getName());
	    $ormAssociationMappings = $classMetaData->getAssociationMappings();

	    // Process single fields into a partial set - Filter fields not avialble on class meta data
	    $selectFields = array_filter($fields, function($offset) use ($classMetaData){
	        if (!is_array($offset) && in_array($offset, $classMetaData->getFieldNames()))
	        {
	            return true;
	        }
	        return false;
	    });

	    // merge required identifier fields with select fields
	    $keyFieldDiff = array_diff($classMetaData->getIdentifierFieldNames(), $selectFields);
	    if (!empty($keyFieldDiff))
	    {
            $addedKeyFields = $keyFieldDiff;
	        $selectFields = array_merge($selectFields, $keyFieldDiff);
	    }

	    if (!empty($selectFields))
	    {
            $qb->addSelect('partial ' . $classAlias . '.{'  . implode(', ', $selectFields) . '}');
	    }

	    // Process relational field with no deeper expose restrictions
	    $relationalFields = array_filter($fields, function($offset) use ($classMetaData) {
            if (!is_array($offset) && in_array($offset, $classMetaData->getAssociationNames()))
	        {
	            return true;
	        }
	        return false;
	    });

	    foreach ($relationalFields as $relationalField)
	    {
            $qb->leftJoin($classAlias . '.' . $relationalField, $this->getAlias($ormAssociationMappings[$relationalField]['targetEntity']));
	        $qb->addSelect($this->getAlias($ormAssociationMappings[$relationalField]['targetEntity']));
	    }

	    foreach ($fields as $key => $value)
	    {
	        if (is_array($value) && isset($ormAssociationMappings[$key]))
	        {
	            $qb->leftJoin($classAlias . '.' . $key, $this->getAlias($ormAssociationMappings[$key]['targetEntity']));
                $qb = $this->registerExpose($value, $qb, $this->em->getClassMetadata($ormAssociationMappings[$key]['targetEntity']), $addedKeyFields[$key], $key);
	        }
	    }

	    //var_dump($addedKeyFields);

	    $this->addedKeyFields = $addedKeyFields;
        return $qb;
	}

	/**
	 * Method used to write to the $data aray.
	 * - 	wraps results in a single entry array keyed by entity name.
	 * 		Eg array(user1, user2) becomes array('users' => array(user1, user2)) - this is useful for a more descriptive output of collection resources
	 * - 	Removes any addition expose fields required for a partial DQL query
	 * @param array $data - the data fetched from the database
	 * @param string $keyName - the key name to use to wrap the data in. If null will attempt to pluralise the entity name on collection request, or singulise on single element request
	 * @return Drest\Query\ResultSet $data
	 */
	protected function createResultSet(array $data, $keyName = null)
	{
	    $classMetaData = $this->matched_route->getClassMetaData();

	    // Recursively remove any additionally added pk fields ($data must be a single record hierarchy. Iterate if we're getting a collection)
	    if ($this->matched_route->isCollection())
	    {
            for ($x = 0; $x < sizeof($data); $x++)
            {
                $this->removeAddedKeyFields($this->addedKeyFields, $data[$x]);
            }
	    } else
	    {
	        $this->removeAddedKeyFields($this->addedKeyFields, $data);
	    }

        if (is_null($keyName))
        {
	        reset($data);
            if (sizeof($data) === 1 && is_string(key($data)))
            {
                // Use the single keyed array as the result set key
                 $keyName = key($data);
                 $data = $data[key($data)];
            } else
            {
                $keyName = ($this->matched_route->isCollection()) ? $classMetaData->getCollectionName() : $classMetaData->getElementName();
            }
        }

	    return ResultSet::create($data, $keyName);
	}

	/**
	 * Functional recursive method to remove any fields added to make the partial DQL work and remove the data
	 * @param array $addedKeyFields
	 * @param array $data - pass by reference
	 */
	protected function removeAddedKeyFields($addedKeyFields, &$data)
	{
	    $addedKeyFields = (array) $addedKeyFields;
	    foreach ($data as $key => $value)
	    {
            if (is_array($value) && isset($addedKeyFields[$key]))
            {
                if (is_int($key))
                {
                    for ($x = 0; $x <= sizeof($value); $x++)
                    {
                        if (isset($data[$x]) && is_array($data[$x]))
                        {
                            $this->removeAddedKeyFields($addedKeyFields[$key], $data[$x]);
                        }
                    }
                } else
                {
                    $this->removeAddedKeyFields($addedKeyFields[$key], $data[$key]);
                }

            } else
            {
                if (is_array($addedKeyFields) && in_array($key, $addedKeyFields))
                {
                    unset($data[$key]);
                }
            }
	    }
	    return $data;
	}

	/**
	 * Get a unique alias name from an entity class name
	 * @param string $className
	 */
	protected function getAlias($className)
	{
        return strtolower(preg_replace("/[^a-zA-Z0-9_\s]/", "", $className));
	}

	/**
	 * Write out as result set on the representation object that was determined - if no representation has been determined - defaults to text
	 * @param Drest\Query\ResultSet $resultSet
	 */
	public function renderDeterminedRepresentation(ResultSet $resultSet)
	{
        if (is_null($this->representation))
        {
            $this->representation = new \Drest\Representation\Text();
        }

        $this->response->setBody($this->representation->output($resultSet));
        $this->response->setHttpHeader('Content-Type', $this->representation->getContentType());
	}

}