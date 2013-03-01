<?php

namespace Drest\Request\Adapter;

use \Zend\Http,
	\Zend\Http\Header\Cookie,
	\Zend\Stdlib\Parameters;

class ZendFramework2 extends AdapterAbstract
{

	/**
	 * @see Drest\Request\Adapter.Request::getCookie()
	 */
	public function getCookie($name = null)
	{
		if ($name !== null && $this->getRequest()->getCookie()->offsetExists($name))
		{
			return $this->getRequest()->getCookie()->offsetGet($name);
		}
		return $this->getRequest()->getCookie()->getAllCookies(\Zend\Http\Cookies::COOKIE_STRING_ARRAY);
	}

	/**
	 * @see Drest\Request\Adapter.Request::setCookie()
	 */
	public function setCookie($name, $value = null)
	{
		if (is_array($name))
		{
			// Empty all cookies
			$this->getRequest()->getCookie()->reset();
			$this->getRequest()->getHeaders()->addHeader(new Cookie($name));
		}
		$this->getRequest()->getCookie()->$name = $value;
	}

	/**
	 * @see Drest\Request\Adapter.Request::getHeaders()
	 */
	public function getHeaders($name = null)
	{
		if ($name == null && $this->getRequest()->getHeaders()->has($name))
		{
			return $this->getRequest()->getHeaders()->get($name);
		}
		return $this->getRequest()->getHeaders()->toArray();
	}

	/**
	 * @see Drest\Request\Adapter.Request::setHeader()
	 */
	public function setHeader($name, $value)
	{
		$this->getRequest()->getHeaders()->addHeader(array($name => $value));
	}

	/**
	 * @see Drest\Request\Adapter.Request::setHeaders()
	 */
	public function setHeaders(array $headers = array())
	{
		$this->getRequest()->getHeaders()->clearHeaders();
		$this->getRequest()->getHeaders()->addHeader($headers);
	}

	/**
	 * @see Drest\Request\Adapter.Request::getPost()
	 */
	public function getPost($name = null)
	{
		if ($name !== null && $this->getRequest()->getPost()->offsetExists($name))
		{
			return $this->getRequest()->getPost($name);
		}
		return $this->getRequest()->getPost()->toArray();
	}

	/**
	 * @see Drest\Request\Adapter.Request::setPost()
	 */
	public function setPost($name, $value = null)
	{
		if (is_array($name))
		{
			$this->getRequest()->setPost(new Parameters($name));
		} else
		{
			$this->getRequest()->getPost()->$name = $value;
		}
	}

	/**
	 * @see Drest\Request\Adapter.Request::getQuery()
	 */
	public function getQuery($name = null)
	{
		if ($name !== null && $this->getRequest()->getQuery()->offsetExists($name))
		{
			return $this->getRequest()->getQuery($name);
		}
		return $this->getRequest()->getQuery()->toArray();
	}


	/**
	 * @see Drest\Request\Adapter.Request::setQuery()
	 */
	public function setQuery($name, $value = null)
	{
		if (is_array($name))
		{
			$this->getRequest()->setPost(new Parameters($name));
		}
	}

	/**
	 * ZendFramework 2 Request object
	 * @return \Zend\Http\Request $request
	 */
	public function getRequest()
	{
		return $this->request;
	}
}