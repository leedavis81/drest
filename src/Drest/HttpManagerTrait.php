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
namespace Drest;

use DrestCommon\Request\Request;
use DrestCommon\Response\Response;
trait HttpManagerTrait
{
    /**
     * Drest request object
     * @var Request $request
     */
    protected $request;

    /**
     * Drest response object
     * @var Response $response
     */
    protected $response;


    /**
     * Set up the HTTP manager
     * @param $request
     * @param $response
     * @param Configuration $config
     */
    public function setUpHttp($request, $response, Configuration $config)
    {
        $this->setRequest(Request::create($request, $config->getRegisteredRequestAdapterClasses()));
        $this->setResponse(Response::create($response, $config->getRegisteredResponseAdapterClasses()));
    }

    /**
     * Get the request object
     * @return Request $request
     */
    public function getRequest()
    {
        if (!$this->request instanceof Request) {
            $this->request = Request::create();
        }

        return $this->request;
    }

    /**
     * Set the request object
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Get the response object
     * @return Response $response
     */
    public function getResponse()
    {
        if (!$this->response instanceof Response) {
            $this->response = Response::create();
        }

        return $this->response;
    }

    /**
     * Set the response object
     * @param Response $response
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
    }
}
