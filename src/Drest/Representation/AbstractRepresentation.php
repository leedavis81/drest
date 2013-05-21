<?php
namespace Drest\Representation;

use Drest\Mapping\RouteMetaData,
    Drest\Query\ResultSet,
    Drest\Configuration,
    Drest\Request;

abstract class AbstractRepresentation implements InterfaceRepresentation
{

    /**
     * Stored Data representation
     * @var string $data
     */
    protected $data;

    /**
     * Current state of the representation
     * @todo: need to register listeners to detect a change in the objects state
     * @var integer $state
     */
    protected $state;


    public function __construct()
    {
        $this->state = self::STATE_CLEAN;
    }


    /**
     * Uses configuration options to determine whether this writer instance is the media type expected by the client
     * @param array $configOptions - configuration options for content detection
     * @param Drest\Request $request - request object
     * @return boolean $result
     */
    final public function isExpectedContent(array $configOptions, Request $request)
    {
	    foreach ($configOptions as $detectContentOption => $detectContentValue)
	    {
	        switch ($detectContentOption)
	        {
                case Configuration::DETECT_CONTENT_HEADER:
                    $headers = explode(',', $request->getHeaders($detectContentValue));
                    foreach ($headers as $headerEntry)
                    {
                        if (false !== ($pos = strpos($headerEntry, ';')))
                        {
                            $headerEntry = substr($headerEntry, 0, $pos);
                        }
                        // See if the header matches for this writer
                        if (in_array(trim($headerEntry), $this->getMatchableAcceptHeaders()))
                        {
                            return true;
                        }
                    }
                break;
	            case Configuration::DETECT_CONTENT_EXTENSION:
	                // See if an extension has been supplied
	                $ext = $request->getExtension();
                    if (!empty($ext) && in_array($request->getExtension(), $this->getMatchableExtensions()))
                    {
                        return true;
                    }
                break;
                case Configuration::DETECT_CONTENT_PARAM:
                    // Inspect the request object for a "format" parameter
                    if (in_array($request->getQuery($detectContentValue), $this->getMatchableFormatParams()))
                    {
                        return true;
                    }
                break;
	        }
	    }
	    return false;
    }

    /**
     * (non-PHPdoc)
     * @see Drest\Representation.InterfaceRepresentation::__toString()
     */
    public function __toString()
    {
        return $this->data;
    }

    /**
     * (non-PHPdoc)
     * @see Drest\Representation.InterfaceRepresentation::output()
     */
    public function output(ResultSet $data)
    {
        $this->write($data);
        return $this->__toString();
    }
}