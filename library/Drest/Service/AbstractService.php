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
	 * Method used to write to the $data aray. Also wraps results in a single entry array keyed by entity name.
	 * Eg array(user1, user2) becomes array('users' => array(user1, user2)) - this is useful for a more descriptive output of collection resources
	 * @return array $data
	 */
	protected function writeData(array $data)
	{
	    $this->clearData();

	    $classMetaData = $this->matched_route->getClassMetaData();

	    $methodName = 'get' . ucfirst(strtolower(RouteMetaData::$contentTypes[$this->matched_route->getContentType()])) . 'Name';
	    if (!method_exists($classMetaData, $methodName))
	    {
	        throw DrestException::unknownContentType($this->matched_route->getContentType());
	    }
	    $this->data = array($classMetaData->$methodName() => $data);
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