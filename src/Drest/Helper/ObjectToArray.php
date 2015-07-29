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
namespace Drest\Helper;


class ObjectToArray
{
    /**
     * Get an objects variables (including private / protected) as an array
     * @param $object
     * @throws \InvalidArgumentException
     * @return array
     */
    public static function execute($object)
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException('To extract variables from an object, you must supply an object.');
        }

        $objectArray = (array) $object;
        $out = json_encode($objectArray);
        $out = preg_replace('/\\\u0000[*a-zA-Z_\x7f-\xff\\\][a-zA-Z0-9_\x7f-\xff\\\]*\\\u0000/', '', $out);

        return json_decode($out, true);
    }
}