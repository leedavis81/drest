<?php
namespace Drest\Service;


use Drest\DrestException;

use Doctrine\ORM\EntityManager,
	Drest\Response,
	Drest\Request,
	Drest\Manager,
	Drest\Mapping\RouteMetaData;

class AbstractService
{

    /**
     * Doctrine Entity Manager
     * @var \Doctrine\ORM\EntityManager $em
     */
    protected $em;

    /**
     * Drest Manager
     * @var \Drest\Manager $dm
     */
    protected $dm;

	/**
	 * Drest request object
	 * @var \Drest\Request $request
	 */
	protected $request;

	/**
	 * Drest response object
	 * @var \Drest\Response $response
	 */
	protected $response;

	/**
	 * When a route object is matched, it's injected into the service class
	 * @var Drest\Mapping\RouteMetaData $route
	 */
	protected $matched_route;

	/**
	 * The data to be returned in the response
	 * @var array $data
	 */
	protected $data;

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

        $this->setRequest($dm->getRequest());
        $this->setResponse($dm->getResponse());
    }

	/**
	 * Inspects the request object and returns the default service method based on the entity type and verb used
	 * Eg. a GET request to a single element will return getElement()
	 * 	   a GET request to a collection element will return getCollection()
	 * 	   a POST request to a single element will return postElement()
	 * @return string $methodName
	 */
	public function getDefaultMethod()
	{
	    if (!$this->matched_route instanceof RouteMetaData)
	    {
            DrestException::noMatchedRouteSet();
	    }

	    $functionName = '';
	    $httpMethod = $this->request->getHttpMethod();
	    switch ($httpMethod)
	    {
	        case Request::METHOD_OPTIONS:
            case Request::METHOD_TRACE:
                $functionName = strtolower($this->request->getHttpMethod()) . 'Request';
	            break;
            case Request::METHOD_CONNECT:
            case Request::METHOD_PATCH:
            case Request::METHOD_PROPFIND:
            case Request::METHOD_HEAD:
                //@todo: support implementation for these
                break;
            default:
                $functionName = strtolower($this->request->getHttpMethod());
                $functionName .= ucfirst(strtolower(RouteMetaData::$contentTypes[$this->matched_route->getContentType()]));
                break;
	    }
	    return $functionName;
	}

	/**
	 * Inject the request object into the service
	 * @param Drest\Request $request
	 */
	public function setRequest(Request $request)
	{
	    $this->request = $request;
	}

	/**
	 * Inject the response object into the service
	 * @param Drest\Response $response
	 */
	public function setResponse(Response $response)
	{
	    $this->response = $response;
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
     * A recursive function to process the specified expose fields
     * @param array $fields - expose fields to process
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param \Doctrine\ORM\Mapping\ClassMetadata $classMetaData
	 * @param string $key - The key of the expose entry being processed
     * @param array $addedKeyFields
     */
	protected function registerExpose($fields, \Doctrine\ORM\QueryBuilder $qb, \Doctrine\ORM\Mapping\ClassMetadata $classMetaData, &$addedKeyFields = array(), $key = null)
	{
	    $classAlias = $this->getAlias($classMetaData->getName());
	    $ormAssociationMappings = $classMetaData->getAssociationMappings();

	    // Process single fields into a partial set
	    $selectFields = array_filter($fields, function($offset) use ($classMetaData){
	        if (!is_array($offset) && in_array($offset, $classMetaData->getFieldNames()))
	        {
	            return true;
	        }
	        return false;
	    });

	    $keyFieldDiff = array_diff($classMetaData->getIdentifierFieldNames(), $selectFields);

	    if (!empty($keyFieldDiff))
	    {

            $addedKeyFields = $keyFieldDiff;
	        $selectFields = array_merge($selectFields, $keyFieldDiff);
	    }

        $qb->addSelect('partial ' . $classAlias . '.{'  . implode(', ', $selectFields) . '}');

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

	    $this->addedKeyFields = $addedKeyFields;
        return $qb;
	}

	/**
	 * Method used to write to the $data aray.
	 * - 	wraps results in a single entry array keyed by entity name.
	 * 		Eg array(user1, user2) becomes array('users' => array(user1, user2)) - this is useful for a more descriptive output of collection resources
	 * - 	Removes any addition expose fields required for a partial DQL query
	 * @return array $data
	 */
	protected function writeData(array $data)
	{
	    $this->clearData();

	    $classMetaData = $this->matched_route->getClassMetaData();

	    // Recursively remove any additionally added pk fields
        $this->removeAddedKeyFields($this->addedKeyFields, $data);

	    $methodName = 'get' . ucfirst(strtolower(RouteMetaData::$contentTypes[$this->matched_route->getContentType()])) . 'Name';
	    if (!method_exists($classMetaData, $methodName))
	    {
	        throw DrestException::unknownContentType($this->matched_route->getContentType());
	    }
	    $this->data = array($classMetaData->$methodName() => $data);
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
	 * Retrieved the data stored on this service
	 * @return array $data
	 */
	public function getData()
	{
        if (!empty($this->data) && sizeof($this->data) > 1)
        {
            throw DrestException::dataMustBeInASingleArrayEntry();
        }
	    return $this->data;
	}

	/**
	 * Clear the data array
	 */
	public function clearData()
	{
	    $this->data = array();
	}

}