<?php
namespace Drest\Writer;

use Drest\Mapping\RouteMetaData,
    Drest\Configuration,
    Drest\Request;

abstract class AbstractWriter implements InterfaceWriter
{

    /**
     * Uses configuration options to determine whether this writer instance is the media type expected by the client
     * @param array $configOptions - configuration options for content detection
     * @param Drest\Request $request - request object
     * @return boolean $result
     */
    final public function isExpectedContent(array $configOptions, Request $request)
    {
	    foreach ($configOptions as $detectContentOption)
	    {
	        switch ($detectContentOption)
	        {
                case Configuration::DETECT_CONTENT_ACCEPT_HEADER:
                    $acceptHeader = explode(';', $request->getHeaders('Accept'));
                    // See if the Accept header matches for this writer
                    if (in_array($request->getHeaders('Accept'), $this->getMatchableAcceptHeaders()))
                    {
                        return true;
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
                    if (in_array($request->getQuery('format'), $this->getMatchableFormatParams()))
                    {
                        return true;
                    }
                break;
	        }
	    }
	    return false;
    }

}