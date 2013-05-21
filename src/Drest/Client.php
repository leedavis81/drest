<?php
namespace Drest;


use Guzzle\Http\Client as GuzzleClient,
    Drest\Representation\AbstractRepresentation,
    Drest\Representation\RepresentationException;

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
     * @param mixed		$representation the data representation to use - can be a string or a class
     */
    public function __construct($endpoint, $representation)
    {
        if (($endpoint = filter_var($endpoint, FILTER_VALIDATE_URL)) === false)
        {
             // @todo: throw exception:  Invalid URL endpoint used
        }

        // The representation type is injected into any object that's pulled through a [GET] request
        if (!is_object($representation))
	    {
            // Check if the class is namespaced, if so instantiate from root
            $className = (strstr($representation, '\\') !== false) ? '\\' . ltrim($representation, '\\') : $representation;
            $className = (!class_exists($className)) ? '\\Drest\\Representation\\' . ltrim($className, '\\') : $className;
            if (!class_exists($className))
            {
                throw RepresentationException::unknownRepresentationClass($representation);
            }
            $this->representationClass = $representation;
        }
        if ($representation instanceof AbstractRepresentation)
        {
            $this->representationClass = get_class($representation);
        }

         // Possibly run a check to ensure the CG classes are upto date - generate a warning if not

        $this->transport = new GuzzleClient($endpoint);

    }

    /**
     *
     * Performs an OPTIONS request to the drest endpoint and generates the retrieved classes in given directory
     * @param string $directory
     * @param string $path - Rather than regenerating all classes, generate all classes necessary for the object utilised under $path
     */
    public function generateClasses($directory, $path = null)
    {

    }

    /**
     * Static call to create an instance of this client
     * @param unknown_type $endpoint
     */
    public static function create($endpoint)
    {
        return new self($endpoint);
    }

    /**
     * Specify the fields you want to retrieve in pipe delimited format.
     * @param string $fields
     */
    public function fields($fields)
    {
        // Check how the representation allows field limited requests

        // Set the header / params based on type
    }

    public function get($path, $fields = null)
    {

        if (!is_null($fields))
        {
            // We need to check the object graph for the request method used to filter expose fields
        }

        $this->transport->get($path, $headers);


        $this->transport->send();
    }

    /**
     * Post an object
     * @param string $path
     * @param Drest\Client\Representation\AbstractRepresentation $object
     */
    public function post($path, $object)
    {

        // render the object into its representation
        $object = $this->updateRepresentation($object);


        $this->transport->post($path, $headers, $body);

        // Handle the response (either errored or 201 created)

        $this->transport->send();
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
     * Attach a single object to the managed dataObjects array
     * @param Drest\Client\Representation\AbstractRepresentation $object
     */
    public function attach(\Drest\Client\Representation\AbstractRepresentation $object)
    {
        $this->dataObjects[] = $object;
    }

    /**
     * Sync all the changes that have been made to all or a single attached object
     * @param Drest\Client\Representation\AbstractRepresentation $object
     */
    public function sync(\Drest\Client\Representation\AbstractRepresentation $object = null)
    {
        if (!is_null($object))
        {

        } else
        {
            // Go through the entire $dataObjects array
        }

    }


    /**
     * Method to handle the response from the guzzle transport layer
     * @param \Guzzle\Http\Message\Response $response
     */
    protected function handleResponse(\Guzzle\Http\Message\Response $response)
    {
        $response->getStatusCode();
    }


    /**
     *
     * update the representation to match the data contained within the data object
     * @return object $object
     */
    protected function updateRepresentation($object)
    {
        // This object must have a representation variable


        var_dump(get_object_vars($object));die;

    }


}