<?php

namespace Drest;

use Exception;

/**
 * Base exception class for all ORM exceptions.
 *
 * @author Roman Borschel <roman@code-factory.org>
 * @since 2.0
 */
class DrestException extends Exception
{

	/**
	 * metadata cache is not configured
	 */
    public static function metadataCacheNotConfigured()
    {
        return new self('Class Metadata Cache is not configured, ensure an instance of Doctrine\Common\Cache\Cache is passed to the Drest\Configuration::setMetadataCacheImpl()');
    }
}