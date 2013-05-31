<?php
namespace Drest;

use Guzzle\Http\Client as GuzzleClient,

    Drest\Representation\AbstractRepresentation,
    Drest\Representation\RepresentationException,
    Drest\Client\Response,
    Drest\Query\ResultSet,
    Drest\Error\ErrorException,
    Guzzle\Http\Exception\BadResponseException;

class Client
{
    /**
     * The transport to be used
     * @var Guzzle\Http\Client
     */
    protected $transport;

    /**
     * The data representation class to used when loading data
     * @var string $representationClass
     */
    protected $representationClass;


    /**
     * Client constructor
     * @param string	$endpoint The rest endpoint to be used
     * @param mixed		$representation the data representation to use for all interactions - can be a string or a class
     */
    public function __construct($endpoint, $representation)
    {
        if (($endpoint = filter_var($endpoint, FILTER_VALIDATE_URL)) === false)
        {
             // @todo: create a an exception extension (ClientException)
             throw new \Exception('Invalid URL endpoint');
        }

        $this->setRepresentationClass($representation);
        $this->transport = new GuzzleClient($endpoint);
    }

    /**
     * The representation class to be used
     * @param mixed $representation
     */
    public function setRepresentationClass($representation)
    {
        if (!is_object($representation))
	    {
            // Check if the class is namespaced, if so instantiate from root
            $className = (strstr($representation, '\\') !== false) ? '\\' . ltrim($representation, '\\') : $representation;
            $className = (!class_exists($className)) ? '\\Drest\\Representation\\' . ltrim($className, '\\') : $className;
            if (!class_exists($className))
            {
                throw RepresentationException::unknownRepresentationClass($representation);
            }
            $this->representationClass = $className;
        } elseif ($representation instanceof AbstractRepresentation)
        {
            $this->representationClass = get_class($representation);
        } else
        {
            throw RepresentationException::needRepresentationToUse();
        }
    }

    /**
     * get an instance of representation class we interacting with
     * @return Drest\Representation\AbstractRepresentation $representation
     */
    protected function getRepresentationInstance()
    {
        return new $this->representationClass();
    }

    /**
     * Get data from a path
     * @param string $path   - the path to be requested
     * @param array $headers - any additional headers you want to send on the request
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
        } catch (BadResponseException $exception)
        {
            throw $this->handleErrorResponse($exception);
        }

        $representation = $representation::createFromString($response->getBody(true));
        return new Response($representation, $response);
    }

    /**
     * Post an object. You can optionally append variables to the path for posting (eg /users?sort=age).
     * @param string 					$path					- the path to post this object to.
     * @param object 					$object					- the object to be posted to given path
     * @param array  					$headers				- an array of headers to send with the request
     * @return Drest\Client\Response 	$response				- Response object with a populated representation instance
     * @throws Drest\Error\ErrorException						- upon the return of any error document from the server
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

        foreach ($this->getVarsFromPath($path) as $key => $value)
        {
            $request->setPostField($key, $value);
        }
        // Bug: Header must be set after adding post fields as Guzzle ammends the Content-Type header info.
        // see: Guzzle\Http\Message\EntityEnclosingRequest::processPostFields()
        $request->setHeader('Content-Type', $representation->getContentType());

        try {
            $response = $this->transport->send($request);
        } catch (\Guzzle\Http\Exception\BadResponseException $exception)
        {
            throw $this->handleErrorResponse($exception);
        }

        return new Response($representation, $response);
    }

    /**
     * Put an object at a set location ($path)
     * @param string $path
     * @param Drest\Client\Representation\AbstractRepresentation $object
     */
    public function put($path, $object)
    {
        $this->transport->put($path, $headers, $body);

        $this->transport->send();
    }

    /**
     * Patch (partially update) an object
     * @param string $path
     * @param Drest\Client\Representation\AbstractRepresentation $object
     */
    public function patch($path, $object)
    {
        $this->transport->patch($path, $headers, $body);

        $this->transport->send();
    }

    /**
     * Delete the passed object
     * @param string $path
     * @param Drest\Client\Representation\AbstractRepresentation $object
     */
    public function delete($path, $object)
    {
        $this->transport->delete($path, $headers, $body);
    }

    /**
     * Get the transport object
     * @return Guzzle\Http\Client $client
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * Handle an error response exception / object
     * @param Guzzle\Http\Exception\BadResponseException $exception
     * @return Drest\Error\ErrorException $error_exception
     */
    protected function handleErrorResponse(BadResponseException $exception)
    {
        $response = \Drest\Response::create($exception->getResponse());
        $errorException = new ErrorException('An error occured on this request', 0, $exception);
        $errorException->setResponse($response);
        foreach ($this->getErrorDocumentClasses() as $errorClass)
        {
            if ($errorClass::getContentType() === $response->getHttpHeader('Content-Type'))
            {
                $errorDocument = $errorClass::createFromString($response->getBody());
                $errorException->setErrorDocument($errorDocument);
                break;
            }
        }
        return $errorException;
    }

    /**
     * Get all registered error document classes
     * @return array $classes
     * @todo: this needs to be cached, or have the classes loaded up on client bootstrap
     */
    protected function getErrorDocumentClasses()
    {
        $classes = array();
        $path = realpath(__DIR__ . DIRECTORY_SEPARATOR . 'Error' . DIRECTORY_SEPARATOR . 'Response');
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file)
        {
            if (!$file->getExtension() === 'php')
            {
                continue;
            }
            $path = $file->getRealPath();
            include $path;
        }

        foreach (get_declared_classes() as $className)
        {
            $reflClass = new \ReflectionClass($className);
            if (array_key_exists('Drest\\Error\\Response\\ResponseInterface', $reflClass->getInterfaces()))
            {
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
        if (isset($urlParts[1]))
        {
            parse_str($urlParts[1], $vars);
        }
        return $vars;
    }
}