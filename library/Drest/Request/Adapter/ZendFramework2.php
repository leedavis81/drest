<?php

namespace Drest\Request\Adapter;

use \Zend\Http,
	\Zend\Stdlib\Parameters,
	Drest\DrestException;

class ZendFramework2 extends AdapterAbstract
{

		/**
	 * (non-PHPdoc)
	 * @see Drest\Request\Adapter.AdapterInterface::getHttpMethod()
	 */
	public function getHttpMethod()
	{
		$const = 'METHOD_' . $this->getRequest()->getMethod();
		if(!defined('parent::' . $const))
		{
			throw DrestException::unknownHttpVerb(get_class($this));
		}
		return constant('parent::' . $const);
	}

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
	 * @see Drest\Request\Adapter.Request::getHeaders()
	 */
	public function getHeaders($name = null)
	{
		if ($name !== null && $this->getRequest()->getHeaders()->has($name))
		{
			return $this->getRequest()->getHeaders()->get($name)->getFieldValue();
		}
		return $this->getRequest()->getHeaders()->toArray();
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
			$this->getRequest()->setQuery(new Parameters($name));
		} else
		{
			$this->getRequest()->getQuery()->$name = $value;
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