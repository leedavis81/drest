<?php
namespace Drest\Service;


use Doctrine\ORM\EntityManager,
	Drest\DrestException,
	Drest\Writer,
	Drest\Request,
	Drest\Manager,
	Drest\Query\ResultSet,
	Drest\Mapping\RouteMetaData;

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
	 * A writer instance determined either from configuration or client media accept type - can be null if none matched
	 * @var Drest\Writer\AbstractWriter $writer
	 */
	protected $writer;

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
            $this->renderDeterminedWriter($this->createResultSet(array()));
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
                $functionName .= ucfirst(strtolower(RouteMetaData::$contentTypes[$this->matched_route->getContentType()]));
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
	 * Set any predetermined writer instance
	 * @param Writer\AbstractWriter $writer
	 */
	public function setWriter(Writer\AbstractWriter $writer)
	{
	    $this->writer = $writer;
	}

	/**
	 * Get the predetermined writer
	 * @param Writer\AbstractWriter $writer
	 */
	public function getWriter()
	{
	    return $this->writer;
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
	    switch ($this->matched_route->getContentType())
	    {
	        case RouteMetaData::CONTENT_TYPE_ELEMENT:
                $this->removeAddedKeyFields($this->addedKeyFields, $data);
	            break;
	        case RouteMetaData::CONTENT_TYPE_COLLECTION:
                for ($x = 0; $x < sizeof($data); $x++)
                {
                    $this->removeAddedKeyFields($this->addedKeyFields, $data[$x]);
                }
	            break;
	    }

        if (is_null($keyName))
        {
    	    $methodName = 'get' . ucfirst(strtolower(RouteMetaData::$contentTypes[$this->matched_route->getContentType()])) . 'Name';
    	    if (!method_exists($classMetaData, $methodName))
    	    {
    	        throw DrestException::unknownContentType($this->matched_route->getContentType());
    	    }
    	    $keyName = $classMetaData->$methodName();
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
	 * Write out as result set on the writer object that was determined
	 * @param Drest\Query\ResultSet $resultSet
	 * @throws DrestException if no writer was determined
	 */
	public function renderDeterminedWriter(ResultSet $resultSet)
	{
        if (is_null($this->writer))
        {
            throw \Drest\Writer\UnableToMatchWriterException::noMatch();
        }

        $this->response->setBody($this->writer->write($resultSet));
        $this->response->setHttpHeader('Content-Type', $this->writer->getContentType());
	}

}