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
namespace Drest\Query;

class InvalidExposeFieldsException extends \Exception
{
    public static function invalidExposeFieldsString()
    {
        return new self('Characters used in the expose fields string are invalid. Only character allowed are [a-z], [A-Z], [0-9], "[", "]", "|" and "_"');
    }


    /**
     * @param $string
     * @return InvalidExposeFieldsException
     */
    public static function unableToParseExposeString($string)
    {
        return new self('Unable to parse the given expose string. Ensure syntax is correct, and square brackets are correctly opened / closed. ' . $string);
    }
}
