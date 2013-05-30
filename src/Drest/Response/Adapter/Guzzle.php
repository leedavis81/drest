<?php

namespace Drest\Response\Adapter;

use Drest\DrestException;
class Guzzle extends AdapterAbstract
{
	/** (non-PHPdoc)
     * @see Drest\Response\Adapter.AdapterInterface::__toString()
     */
    public function toString()
    {
        return $this->getResponse()->__toString();
    }

	/** (non-PHPdoc)
     * @see Drest\Response\Adapter.AdapterInterface::getHttpHeader()
     */
    public function getHttpHeader($name = null)
    {
		if ($name !== null && $this->getResponse()->hasHeader($name))
		{
            return $this->getResponse()->getHeader($name, true);
		}

		if (($this->getResponse()->getHeaders()->count() === 0))
		{
		    return array();
		} else
		{
		    return array_map(function($item){
		        return implode(', ', $item);
		    }, $this->getResponse()->getHeaders()->getAll());
		}
    }

	/** (non-PHPdoc)
     * @see Drest\Response\Adapter.AdapterInterface::setHttpHeader()
     */
    public function setHttpHeader($name, $value = null)
    {
        $value = (array) $value;
    	if (is_array($name))
		{
		    foreach ($this->getResponse()->getHeaders(false) as $key => $value)
		    {
		        $this->getResponse()->removeHeader($key);
		    }
		    $this->getResponse()->addHeaders($name);
		} else
		{
		    $this->getResponse()->addHeader($name, $value);
		}
    }

    /**
     * (non-PHPdoc)
     * @see Drest\Response\Adapter.AdapterInterface::getBody()
     */
    public function getBody()
    {
        return $this->getResponse()->getBody(true);
    }

	/** (non-PHPdoc)
     * @see Drest\Response\Adapter.AdapterInterface::setBody()
     */
    public function setBody($body)
    {
        $this->getResponse()->setBody($body);
    }

	/** (non-PHPdoc)
     * @see Drest\Response\Adapter.AdapterInterface::getStatusCode()
     */
    public function getStatusCode()
    {
        return $this->getResponse()->getStatusCode();
    }

	/** (non-PHPdoc)
     * @see Drest\Response\Adapter.AdapterInterface::setStatusCode()
     */
    public function setStatusCode($code, $text)
    {
        $this->getResponse()->setStatus($code, $text);
    }

	/**
	 * Guzzle Response object
	 * @return \Guzzle\Http\Message\Response $response
	 */
	public function getResponse()
	{
		return $this->response;
	}
}