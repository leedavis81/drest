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
namespace Drest\Route;

class MultipleRoutesException extends \Exception
{
    public static function multipleRoutesFound($routes)
    {
        $helperText = '';
        foreach ($routes as $route) {
            /* @var \Drest\Mapping\RouteMetaData $route */
            $helperText .= '"' . $route->getName() . '"';
        }

        return new self('Multiple routes have matched to this request. See route definitions for: ' . $helperText);
    }
}
