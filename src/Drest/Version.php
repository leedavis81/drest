<?php

namespace Drest;

/**
 * Store current Drest version
 */
class Version
{
    /**
     * Current Version
     */
    const VERSION = '0.0.1';

    /**
     * Compares a previous version with the current one.
     *
     * @param string $version version to compare.
     * @return integer $result (-1 if older, 0 the same, 1 if newer)
     */
    public static function compare($version)
    {
        return version_compare($version, self::VERSION);
    }
}
