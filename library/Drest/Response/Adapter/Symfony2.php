<?php

namespace Drest\Response\Adapter;

use Drest\DrestException;
class Symfony2 extends AdapterAbstract
{
	/** (non-PHPdoc)
     * @see Drest\Response\Adapter.AdapterInterface::__toString()
     */
    public function __toString()
    {
        return $this->response;
    }

	/** (non-PHPdoc)
     * @see Drest\Response\Adapter.AdapterInterface::getHttpHeader()
     */
    public function getHttpHeader($name = null)
    {
		if ($name !== null && $this->getResponse()->headers->has($name))
		{
			return $this->getResponse()->headers->get($name);
		}
		return $this->getRequest()->headers->all();
    }

	/** (non-PHPdoc)
     * @see Drest\Response\Adapter.AdapterInterface::setHttpHeader()
     */
    public function setHttpHeader($name, $value = null)
    {
    	if (is_array($name))
		{
			$this->getResponse()->headers->replace($name);
		} else
		{
			$this->getResponse()->headers->set($name, $value);
		}
    }

    /**
     * (non-PHPdoc)
     * @see Drest\Response\Adapter.AdapterInterface::getBody()
     */
    public function getBody()
    {
        return $this->getResponse()->getContent();
    }

	/** (non-PHPdoc)
     * @see Drest\Response\Adapter.AdapterInterface::setBody()
     */
    public function setBody($body)
    {
        $this->getResponse()->setContent($body);
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
        $this->getResponse()->setStatusCode($code, $text);
    }

	/**
	 * Symfony 2 Request object
	 * @return \Symfony\Component\HttpFoundation\Response $response
	 */
	public function getResponse()
	{
		return $this->response;
	}
}