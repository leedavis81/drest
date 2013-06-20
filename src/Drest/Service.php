<?php
namespace Drest;

use Doctrine\ORM\EntityManager;
use Drest\Error\Handler\AbstractHandler;
use Drest\Mapping\RouteMetaData;
use Drest\Query\ResultSet;
use Drest\Representation;
use Drest\Service\Action\AbstractAction;

class Service
{
    /**
     * Doctrine Entity Manager
     * @var EntityManager $em
     */
    protected $em;

    /**
     * Drest Manager
     * @var Manager $dm
     */
    protected $dm;

    /**
     * When a route object is matched, it's injected into the service class
     * @var RouteMetaData $route
     */
    protected $matched_route;

    /**
     * Service action instance determined
     * @var AbstractAction $service_action
     */
    private $service_action;

    /**
     * A representation instance determined either from configuration or client media accept type - can be null if none matched
     * Note this will be present on both a fetch (GET) request and on a push (POST / PUT) request if using the drest client
     * @var Representation\AbstractRepresentation $representation
     */
    protected $representation;

    /**
     * The error handler instance
     * @var AbstractHandler $error_handler
     */
    protected $error_handler;


    /**
     * Initialise a new instance of a Drest service
     * @param EntityManager $em The EntityManager to use.
     * @param Manager $dm The Drest Manager object
     */
    public function __construct(EntityManager $em, Manager $dm)
    {
        $this->em = $em;
        $this->dm = $dm;
    }

    /**
     * Called on successful routing of a service call
     * Prepares the service to a request to be rendered
     * @todo: Fires off any events registered to preLoad
     * @return boolean $result - if false then fail fast no call to runCallMethod() should be made.
     */
    public function setupRequest()
    {
        // Make sure we have a route matched
        //@todo: this shouldn't throw an exception, pass failure to the error handler
        if (!$this->matched_route instanceof RouteMetaData) {
            DrestException::noMatchedRouteSet();
        }

        // If its a GET request and no expose fields are present, fail early
        $expose = $this->matched_route->getExpose();
        if ($this->getRequest()->getHttpMethod() == Request::METHOD_GET &&
            (empty($expose) || (sizeof($expose) == 1 && empty($expose[0])))
        ) {
            $this->renderDeterminedRepresentation($this->getActionInstance()->createResultSet(array()));
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
     * Get an instance of the action class to be used
     * @throws DrestException
     * @return AbstractAction $action
     */
    protected function getActionInstance()
    {
        if (!isset($this->service_action)) {
            $actionClass = $this->matched_route->getActionClass();
            if (is_null($actionClass)) {
                // Run default action class
                $this->service_action = $this->getDefaultAction();
            } else {
                if (!class_exists($actionClass)) {
                    throw DrestException::unknownActionClass($actionClass);
                }
                $this->service_action = new $actionClass($this);
            }

            if (!$this->service_action instanceof AbstractAction) {
                throw DrestException::actionClassNotAnInstanceOfActionAbstract($this->service_action);
            }
        }
        return $this->service_action;
    }

    /**
     * Run the call method required on this service object
     */
    final public function runCallMethod()
    {
        if (($return = $this->getActionInstance()->execute()) instanceof ResultSet) {
            $this->renderDeterminedRepresentation($return);
        }
    }

    /**
     * Gets an instance of the "default" action based of request information
     * @throws DrestException
     * @return AbstractAction $action
     */
    protected function getDefaultAction()
    {
        $httpMethod = $this->getRequest()->getHttpMethod();
        $className = '\\Drest\\Service\\Action\\' . ucfirst(strtolower($httpMethod));
        switch ($httpMethod) {
            case Request::METHOD_GET:
            case Request::METHOD_DELETE:
                $className .= ($this->matched_route->isCollection()) ? 'Collection' : 'Element';
                break;
            default:
                $className .= 'Element';
                break;
        }
        if (!class_exists($className)) {
            throw DrestException::unknownActionClass($className);
        }

        return new $className($this);
    }

    /**
     * Set the matched route object
     * @param RouteMetaData $matched_route
     */
    public function setMatchedRoute(RouteMetaData $matched_route)
    {
        $this->matched_route = $matched_route;
    }

    /**
     * Get the route object that was matched
     * @return RouteMetaData $matched_route
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
     * @return Representation\AbstractRepresentation
     */
    public function getRepresentation()
    {
        return $this->representation;
    }

    /**
     * Set the error handler object
     * @param AbstractHandler $error_handler
     */
    public function setErrorHandler(AbstractHandler $error_handler)
    {
        $this->error_handler = $error_handler;
    }

    /**
     * Get the entity manager
     * @return EntityManager $em
     */
    public function getEntityManager()
    {
        return $this->em;
    }

    /**
     * Get the Drest Manager
     * @return Manager $dm
     */
    public function getDrestManager()
    {
        return $this->dm;
    }

    /**
     * Get the response object
     * @return Response $response
     */
    public function getResponse()
    {
        return $this->dm->getResponse();
    }

    /**
     * Get the request object
     * @return Request $request
     */
    public function getRequest()
    {
        return $this->dm->getRequest();
    }

    /**
     * Handle an error - set the resulting error document to the response object
     * @param \Exception $e
     * @param integer $defaultResponseCode the default response code to use if no match on exception type occurs
     * @param Error\Response\ResponseInterface $errorDocument
     * @return ResultSet the error result set
     */
    public function handleError(\Exception $e, $defaultResponseCode = 500, Error\Response\ResponseInterface $errorDocument = null)
    {
        if (is_null($errorDocument)) {
            $errorDocument = $this->representation->getDefaultErrorResponse();
        }

        $this->error_handler->error($e, $defaultResponseCode, $errorDocument);

        $this->getResponse()->setStatusCode($this->error_handler->getResponseCode());
        $this->getResponse()->setHttpHeader('Content-Type', $errorDocument::getContentType());
        $this->getResponse()->setBody($errorDocument->render());
    }


    /**
     * Write out as result set on the representation object that was determined - if no representation has been determined - defaults to text
     * @param ResultSet $resultSet
     */
    public function renderDeterminedRepresentation(ResultSet $resultSet)
    {
        $this->getResponse()->setBody($this->representation->output($resultSet));
        $this->getResponse()->setHttpHeader('Content-Type', $this->representation->getContentType());
    }
}