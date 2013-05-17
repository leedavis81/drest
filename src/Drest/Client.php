<?php
namespace Drest;


use Guzzle\Http\Client as GuzzleClient,
    Drest\Client\InterfaceClient;

class Client extends GuzzleClient implements InterfaceClient
{

    /**
     * Representation class
     * @var Drest\Client\Representation\AbstractRepresentation $representation
     */
    protected $representation;


    /**
     * Client constructor
     * @param string           $baseUrl Base URL of the web service
     * @param array|Collection $config  Configuration settings
     */
    public function __construct($baseUrl = '', $config = null)
    {
        parent::__construct($baseUrl, $config);
        return $this;
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








    /**
	 * Send the request
     */
    public function send()
    {
        $this->get()

        $response = parent::send();

        // If its a GET request, update the representation

        if ($response instanceof \Guzzle\Http\Message\Response)
        {
            //
            $response->getStatusCode();
        }


    }


    /**
     *
     * Calls the end point, then passes the response into the representation
     */
    protected function updateRepresentation()
    {


    }



    /**
     * Get the orginal object
     */
    public function getObject()
    {
        return $this->representation;
    }
}