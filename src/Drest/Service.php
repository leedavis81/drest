<?php
namespace Drest;

use Drest\Representation\RepresentationException;

use Doctrine\ORM\EntityManager,
    Drest\Service\Action\AbstractAction,
	Drest\Query\ResultSet,
	Drest\Mapping\RouteMetaData,
	Drest\Error\Handler\AbstractHandler;

class Service
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
	 * When a route object is matched, it's injected into the service class
	 * @var Drest\Mapping\RouteMetaData $route
	 */
	protected $matched_route;

	/**
	 * A representation instance determined either from configuration or client media accept type - can be null if none matched
	 * Note this will be present on both a fetch (GET) request and on a push (POST / PUT) request if using the drest client
	 * @var Drest\Representation\AbstractRepresentation $representation
	 */
	protected $representation;

	/**
	 * The error handler instance
	 * @var Drest\Error\Handler\AbstractHandler $error_handler
	 */
	protected $error_handler;


    /**
     * Initialise a new instance of a Drest service
     * @param \Doctrine\ORM\EntityManager $em The EntityManager to use.
     * @param \Drest\Manager $dm The Drest Manager object
     */
    public function __construct(EntityManager $em, Manager $dm)
    {
        $this->em = $em;
        $this->dm = $dm;
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
        if ($this->getRequest()->getHttpMethod() == Request::METHOD_GET &&
            (empty($expose) || (sizeof($expose) == 1 && empty($expose[0])))
           )
        {
            $this->renderDeterminedRepresentation($this->createResultSet(array()));
            return false;
        }

	    // @todo: Run verb type setup operations (do we want to break down by verb? or entity name? - evm)
//	    switch ($this->getRequest()->getHttpMethod())
//	    {
//	        case Request::METHOD_GET:
//	            break;
//	    }

	    return true;
    }

    /**
     * Run the call method required on this service object
     */
    final public function runCallMethod()
    {
        $actionClass = $this->matched_route->getActionClass();
        if (is_null($actionClass))
        {
            // Run default action class
            $actionInstance = $this->getDefaultAction();
        } else
        {
            if (!class_exists($actionClass))
            {
                throw DrestException::unknownActionClass($actionClass);
            }
            $actionInstance = new $actionClass($this);
        }

        if (!$actionInstance instanceof AbstractAction)
        {
            throw DrestException::actionClassNotAnInstanceOfActionAbstract($actionInstance);
        }

        if (($return = $actionInstance->execute()) instanceof ResultSet)
        {
            $this->renderDeterminedRepresentation($return);
        }
    }

    /**
     * Gets an instance of the "default" action based of request information
     * @return Drest\Service\Action\AbstractAction $action
     */
    protected function getDefaultAction()
    {
        $httpMethod = $this->getRequest()->getHttpMethod();
        $className = '\\Drest\\Service\\Action\\' . ucfirst(strtolower($httpMethod));
        switch ($httpMethod)
	    {
            case Request::METHOD_GET:
            case Request::METHOD_DELETE:
                $className .= ($this->matched_route->isCollection()) ? 'Collection' : 'Element';
                break;
            default:
                $className .= 'Element';
                break;
	    }
	    if (!class_exists($className))
	    {
            throw DrestException::unknownActionClass($className);
	    }

	    return new $className($this);
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
	 * @param Drest\Error\Handler\AbstractHandler $error_handler
	 */
	public function setErrorHandler(AbstractHandler $error_handler)
	{
	    $this->error_handler = $error_handler;
	}

	/**
	 * Get the entity manager
	 * @return Doctrine\ORM\EntityManager $em
	 */
	public function getEntityManager()
	{
	    return $this->em;
	}

	/**
	 * Get the Drest Manager
	 * @return Drest\Manager $dm
	 */
	public function getDrestManager()
	{
	    return $this->dm;
	}

	/**
	 * Get the response object
	 * @return Drest\Response $response
	 */
	public function getResponse()
	{
	    return $this->dm->getResponse();
	}

	/**
	 * Get the request object
	 * @return Drest\Request $request
	 */
	public function getRequest()
	{
        return $this->dm->getRequest();
	}

	/**
	 * Handle an error - set the resulting error document to the response object
	 * @param \Exception $e
  	 * @param $defaultResponseCode the default response code to use if no match on exception type occurs
  	 * @param Drest\Error\Response\ResponseInterface $errorDocument
  	 * @return ResultSet the error result set
	 */
	public function handleError(\Exception $e, $defaultResponseCode = 500, Drest\Error\Response\ResponseInterface $errorDocument = null)
	{
	    if (is_null($errorDocument))
	    {
	        $errorDocument = $this->representation->getDefaultErrorResponse();
	    }

	    $this->error_handler->error($e, $defaultResponseCode, $errorDocument);

	    $this->getResponse()->setStatusCode($this->error_handler->getReponseCode());
        $this->getResponse()->setHttpHeader('Content-Type', $errorDocument::getContentType());
	    $this->getResponse()->setBody($errorDocument->render());
	}


	/**
	 * Write out as result set on the representation object that was determined - if no representation has been determined - defaults to text
	 * @param Drest\Query\ResultSet $resultSet
	 */
	public function renderDeterminedRepresentation(ResultSet $resultSet)
	{
        $this->getResponse()->setBody($this->representation->output($resultSet));
        $this->getResponse()->setHttpHeader('Content-Type', $this->representation->getContentType());
	}
}