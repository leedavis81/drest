<?php
namespace Drest;

use Drest\Client\Response;
use Drest\Error\ErrorException;
use Drest\Query\ResultSet;
use Drest\Representation\AbstractRepresentation;
use Drest\Representation\RepresentationException;
use Guzzle\Http\Client as GuzzleClient;
use Guzzle\Http\Exception\BadResponseException;

class Client
{
    /**
     * The transport to be used
     * @var GuzzleClient
     */
    protected $transport;

    /**
     * The data representation class to used when loading data
     * @var string $representationClass
     */
    protected $representationClass;

    /**
     * Cached error response class
     * Cleared whenever a new representation type is set
     * Redetermined whenever empty and handleErrorResponse() is called
     * @var string $errorResponseClass
     */
    protected $errorResponseClass;


    /**
     * Client constructor
     * @param string $endpoint The rest endpoint to be used
     * @param mixed $representation the data representation to use for all interactions - can be a string or a class
     * @throws \Exception
     */
    public function __construct($endpoint, $representation)
    {
        if (($endpoint = filter_var($endpoint, FILTER_VALIDATE_URL)) === false) {
            // @todo: create a an exception extension (ClientException)
            throw new \Exception('Invalid URL endpoint');
        }

        $this->setRepresentationClass($representation);
        $this->transport = new GuzzleClient($endpoint);
    }

    /**
     * The representation class to be used
     * @param mixed $representation
     * @throws Representation\RepresentationException
     */
    public function setRepresentationClass($representation)
    {
        $this->errorResponseClass = null;
        if (!is_object($representation)) {
            // Check if the class is namespaced, if so instantiate from root
            $className = (strstr($representation, '\\') !== false) ? '\\' . ltrim($representation, '\\') : $representation;
            $className = (!class_exists($className)) ? '\\Drest\\Representation\\' . ltrim($className, '\\') : $className;
            if (!class_exists($className)) {
                throw RepresentationException::unknownRepresentationClass($representation);
            }
            $this->representationClass = $className;
        } elseif ($representation instanceof AbstractRepresentation) {
            $this->representationClass = get_class($representation);
        } else {
            throw RepresentationException::needRepresentationToUse();
        }
    }

    /**
     * get an instance of representation class we interacting with
     * @return AbstractRepresentation $representation
     */
    protected function getRepresentationInstance()
    {
        return new $this->representationClass();
    }

    /**
     * Get data from a path
     * @param string $path   - the path to be requested
     * @param array $headers - any additional headers you want to send on the request
     * @throws ErrorException
     * @return Response
     */
    public function get($path, array $headers = array())
    {
        $representation = $this->getRepresentationInstance();

        $headers['Accept'] = $representation->getContentType();
        $request = $this->transport->get(
            $path,
            $headers
        );

        try {
            $response = $this->transport->send($request);
        } catch (BadResponseException $exception) {
            throw $this->handleErrorResponse($exception);
        }

        $representation = $representation::createFromString($response->getBody(true));
        return new Response($representation, $response);
    }

    /**
     * Post an object. You can optionally append variables to the path for posting (eg /users?sort=age).
     * @param string $path                    - the path to post this object to.
     * @param object $object                    - the object to be posted to given path
     * @param array $headers                - an array of headers to send with the request
     * @return Response                $response                - Response object with a populated representation instance
     * @throws ErrorException                                    - upon the return of any error document from the server
     */
    public function post($path, &$object, array $headers = array())
    {
        $representation = $this->getRepresentationInstance();
        $representation->update($object);

        $request = $this->transport->post(
            $path,
            $headers,
            $representation->__toString()
        );

        foreach ($this->getVarsFromPath($path) as $key => $value) {
            $request->setPostField($key, $value);
        }
        // Bug: Header must be set after adding post fields as Guzzle amends the Content-Type header info.
        // see: Guzzle\Http\Message\EntityEnclosingRequest::processPostFields()
        $request->setHeader('Content-Type', $representation->getContentType());

        try {
            $response = $this->transport->send($request);
        } catch (BadResponseException $exception) {
            throw $this->handleErrorResponse($exception);
        }

        return new Response($representation, $response);
    }

    /**
     * Put an object at a set location ($path)
     * @param string $path                    - the path to post this object to.
     * @param object $object                    - the object to be posted to given path
     * @param array $headers                - an array of headers to send with the request
     * @return Response    $response                            - Response object with a populated representation instance
     * @throws ErrorException                                   - upon the return of any error document from the server
     */
    public function put($path, &$object, array $headers = array())
    {
        $representation = $this->getRepresentationInstance();
        $representation->update($object);

        $request = $this->transport->put(
            $path,
            $headers,
            $representation->__toString()
        );

        foreach ($this->getVarsFromPath($path) as $key => $value) {
            $request->setPostField($key, $value);
        }

        $request->setHeader('Content-Type', $representation->getContentType());

        try {
            $response = $this->transport->send($request);
        } catch (BadResponseException $exception) {
            throw $this->handleErrorResponse($exception);
        }

        return new Response($representation, $response);
    }

    /**
     * Patch (partial update) an object at a set location ($path)
     * @param string $path the path to post this object to.
     * @param object $object the object to be posted to given path
     * @param array $headers an array of headers to send with the request
     * @return \Drest\Client\Response $response Response object with a populated representation instance
     * @throws ErrorException upon the return of any error document from the server
     */
    public function patch($path, &$object, array $headers = array())
    {
        $representation = $this->getRepresentationInstance();
        $representation->update($object);

        $request = $this->transport->patch(
            $path,
            $headers,
            $representation->__toString()
        );

        foreach ($this->getVarsFromPath($path) as $key => $value) {
            $request->setPostField($key, $value);
        }

        $request->setHeader('Content-Type', $representation->getContentType());

        try {
            $response = $this->transport->send($request);
        } catch (BadResponseException $exception) {
            throw $this->handleErrorResponse($exception);
        }

        return new Response($representation, $response);
    }

    /**
     * Delete the passed object
     * @param string $path the path to post this object to.
     * @param array $headers an array of headers to send with the request
     */
    public function delete($path, array $headers = array())
    {
        $this->transport->delete($path, $headers);
    }

    /**
     * Get the transport object
     * @return GuzzleClient $client
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * Handle an error response exception / object
     * @param BadResponseException $exception
     * @return ErrorException $error_exception
     */
    protected function handleErrorResponse(BadResponseException $exception)
    {
        $response = Response::create($exception->getResponse());
        $errorException = new ErrorException('An error occurred on this request', 0, $exception);
        $errorException->setResponse($response);

        $contentType = $response->getHttpHeader('Content-Type');
        if (!empty($contentType))
        {
            $errorClass = $this->getErrorDocumentClass($contentType);
            if (!is_null($errorClass))
            {
                $errorDocument = $errorClass::createFromString($response->getBody());
                $errorException->setErrorDocument($errorDocument);
            }
        }

        return $errorException;
    }

    /**
     * Get the Error Document class from the response's content type
     * @param $contentType
     * @return string|null
     */
    protected function getErrorDocumentClass($contentType)
    {
        if (empty($this->errorResponseClass))
        {
            foreach ($this->getErrorDocumentClasses() as $errorClass) {
                /* @var \Drest\Error\Response\ResponseInterface $errorClass */
                if ($errorClass::getContentType() == $contentType) {
                    $this->errorResponseClass = $errorClass;
                    break;
                }
            }
        }
        return $this->errorResponseClass;
    }

    /**
     * Get all registered error document classes
     * @return array $classes
     */
    protected function getErrorDocumentClasses()
    {
        $classes = array();
        $path = realpath(__DIR__ . DIRECTORY_SEPARATOR . 'Error' . DIRECTORY_SEPARATOR . 'Response');
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            /* @var \SplFileInfo $file */
            if (!$file->getExtension() === 'php') {
                continue;
            }
            $path = $file->getRealPath();

            if (!empty($path)) {
                include_once $path;
            }
        }

        foreach (get_declared_classes() as $className) {
            $reflClass = new \ReflectionClass($className);
            if (array_key_exists('Drest\\Error\\Response\\ResponseInterface', $reflClass->getInterfaces())) {
                $classes[] = $className;
            }
        }

        return $classes;
    }

    /**
     * Get the variables from a path
     * @param string $path
     * @return array $vars
     */
    protected function getVarsFromPath($path)
    {
        $vars = array();
        $urlParts = preg_split('/[?]/', $path);
        if (isset($urlParts[1])) {
            parse_str($urlParts[1], $vars);
        }
        return $vars;
    }
}