<?php
/**
 * This file is part of the Drest package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Lee Davis
 * @copyright Copyright (c) Lee Davis <@leedavis81>
 * @link https://github.com/leedavis81/drest/blob/master/LICENSE
 * @license http://opensource.org/licenses/MIT The MIT X License (MIT)
 */
namespace Drest\Mapping\Driver;



/**
 * Base exception class for all Drest exceptions.
 */
class DriverException extends \Exception
{
    public static function configurationFileDoesntExist($path)
    {
        return new self('The configuration file doesn\'t exist at path: ' . $path);
    }

    public static function configurationFileIsInvalid($format)
    {
        return new self('The configuration file returns an invalid format. It should be a "' . $format . '"" file.');
    }
}