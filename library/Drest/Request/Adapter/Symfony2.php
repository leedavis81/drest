<?php

namespace Drest\Request\Adapter;

class Symfony2 extends AdapterAbstract
{

	/**
	 * @see Drest\Request\Adapter.Request::getCookie()
	 */
	public function getCookie($name = null)
	{
		if ($name !== null && $this->getRequest()->cookies->has($name))
		{
			return $this->getRequest()->cookies->get($name);
		}
		return $this->getRequest()->cookies->all();
	}

	/**
	 * @see Drest\Request\Adapter.Request::setCookie()
	 */
	public function setCookie($name, $value = null)
	{
		if (is_array($name))
		{
			$this->getRequest()->cookies->replace($name);
		} else
		{
			$this->getRequest()->cookies->set($name, $value);
		}
	}

	/**
	 * @see Drest\Request\Adapter.Request::getHeaders()
	 */
	public function getHeaders($name = null)
	{
		if ($name !== null && $this->getRequest()->headers->has($name))
		{
			return $this->getRequest()->headers->get($name);
		}
		return $this->getRequest()->headers->all();
	}

	/**
	 * @see Drest\Request\Adapter.Request::setHeader()
	 */
	public function setHeader($name, $value)
	{
		$this->getRequest()->headers->set($name, $value);
	}

	/**
	 * @see Drest\Request\Adapter.Request::setHeaders()
	 */
	public function setHeaders(array $headers = array())
	{
		$this->getRequest()->headers->replace($headers);
	}

	/**
	 * @see Drest\Request\Adapter.Request::setPost()
	 */
	public function setPost($name, $value = null)
	{
		if (is_array($name))
		{
			$this->getRequest()->request->replace($name);
		} else
		{
			$this->getRequest()->request->set($name, $value);
		}
	}

	/**
	 * @see Drest\Request\Adapter.Request::getPost()
	 */
	public function getPost($name = null)
	{
		if ($name !== null && $this->getRequest()->request->has($name))
		{
			return $this->getRequest()->request->get($name);
		}
		return $this->getRequest()->request->all();
	}

	/**
	 * @see Drest\Request\Adapter.Request::getQuery()
	 */
	public function getQuery($name = null)
	{
		if ($name !== null && $this->getRequest()->query->has($name))
		{
			return $this->getRequest()->query->get($name);
		}
		return $this->getRequest()->query->all();
	}

	/**
	 * @see Drest\Request\Adapter.Request::setQuery()
	 */
	public function setQuery($name, $value = null)
	{
		if (is_array($name))
		{
			$this->getRequest()->query->replace($name);
		} else
		{
			$this->getRequest()->query->set($name, $value);
		}
	}

	/**
	 * Symfony 2 Request object
	 * @return \Symfony\Component\HttpFoundation\Request $request
	 */
	public function getRequest()
	{
		return $this->request;
	}
}