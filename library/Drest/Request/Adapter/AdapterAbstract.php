<?php

namespace Drest\Request\Adapter;

abstract class AdapterAbstract implements AdapterInterface
{

	/**
	 * Abstracted request object (could be zf / symfony object)
	 * @var object $request
	 */
	protected $request;

	/**
	 * Contains an array of parameters extracted from the route (only populated once a route has been matched)
	 * @var array $routeParams
	 */
	protected $routeParams = array();

	/**
	 * Construct an instance of request adapter
	 * @param object $request
	 */
	public function __construct($request)
	{
		$this->request = $request;
	}

	/** (non-PHPdoc)
	 * @see Drest\Request\Adapter.Request::getParams()
	 */
	public function getParams()
	{
		return array_merge($this->getRouteParam(), $this->getCookie(), $this->getPost(), $this->getQuery());
	}

	/** (non-PHPdoc)
	 * @see Drest\Request\Adapter.Request::setRouteParam()
	 */
	public function setRouteParam($name, $value = null)
	{
		if (is_array($name))
		{
			$this->routeParams = $name;
		} else
		{
			$this->routeParams[$name] = $value;
		}
	}

	/* (non-PHPdoc)
	 * @see Drest\Request\Adapter.AdapterInterface::getRouteParam()
	 */
	public function getRouteParam($name = null)
	{
		if ($name !== null && isset($this->routeParams[$name]))
		{
			return $this->routeParams[$name];
		}
		return $this->routeParams;
	}

	/**
	 * (non-PHPdoc)
	 * @see Drest\Request\Adapter.AdapterInterface::getPath()
	 */
	public function getPath()
	{
        $urlParts = preg_split('/[!?#.]/', $this->getUri());
        return $urlParts[0];
	}

	/**
	 * (non-PHPdoc)
	 * @see Drest\Request\Adapter.AdapterInterface::getExtension()
	 */
	public function getExtension()
	{
         $urlParts = preg_split('/[.]/', $this->getUri());
         if (sizeof($urlParts) > 1)
         {
             return $urlParts[sizeof($urlParts)-1];
         }
         return '';
	}
}