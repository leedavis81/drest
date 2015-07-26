<?php
namespace Drest;

use Doctrine\ORM\EntityManager;
use Drest\Mapping\RouteMetaData;
use Drest\Service\Action\AbstractAction;
use DrestCommon\Error\Handler\AbstractHandler;
use DrestCommon\Error\Response\ResponseInterface;
use DrestCommon\Representation;
use DrestCommon\Request\Request;
use DrestCommon\Response\Response;
use DrestCommon\ResultSet;

/**
 * Class Service handles all attributes of a single service call.
 * Is also injected into custom service actions
 * @package Drest
 */
class Service
{
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
     * @param Manager       $dm The Drest Manager object
     */
    public function __construct(Manager $dm)
    {
        $this->dm = $dm;
    }

    /**
     * Called on successful routing of a service call
     * Prepares the service to a request to be rendered
     *
     * @return boolean $result - if false then fail fast no call to runCallMethod() should be made.
     */
    public function setupRequest()
    {
        // Make sure we have a route matched (this should caught and an exception thrown on Manager::determineRoute())
        if (!$this->matched_route instanceof RouteMetaData) {
            return false;
        }

        // If its a GET request and no expose fields are present, fail early
        $expose = $this->matched_route->getExpose();
        if ($this->getRequest()->getHttpMethod() == Request::METHOD_GET &&
            (empty($expose) || (sizeof($expose) == 1 && empty($expose[0])))
        ) {
            $this->renderDeterminedRepresentation($this->getActionInstance()->createResultSet(array()));

            return false;
        }

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
        // dispatch preServiceAction event
        $this->dm->getEventManager()->dispatchEvent(
            Event\Events::PRE_SERVICE_ACTION,
            new Event\PreServiceActionArgs($this)
        );

        $return = $this->getActionInstance()->execute();

        // dispatch postServiceAction event
        $this->dm->getEventManager()->dispatchEvent(
            Event\Events::POST_SERVICE_ACTION,
            new Event\PostServiceActionArgs($this)
        );

        if ($return instanceof ResultSet) {
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
     * This will return the default manager, to choose a specific one use getEntityManagerRegistry()
     * @return EntityManager $em
     */
    public function getEntityManager()
    {
        return $this->getEntityManagerRegistry()->getManager();
    }

    /**
     * Get the entity manager registry
     * @return \Doctrine\Common\Persistence\ManagerRegistry
     */
    public function getEntityManagerRegistry()
    {
        return $this->dm->getEntityManagerRegistry();
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
     * @param  \Exception        $e
     * @param  integer           $defaultResponseCode the default response code to use if no match on exception type occurs
     * @param  ResponseInterface $errorDocument
     * @return ResultSet         the error result set
     */
    public function handleError(\Exception $e, $defaultResponseCode = 500, ResponseInterface $errorDocument = null)
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
