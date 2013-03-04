<?php
namespace Drest;

use Drest\DrestException,
	Drest\Request\Adapter;


class Request
{

	/**
	 * Adapter class used for request handling
	 * @var Drest\Request\Adapter\AdapterAbstract $adapter
	 */
	protected $adapter;


	/**
	 * Construct an instance of Drest Request object
	 * @param mixed $request prefered router type
	 */
	public function __construct($request_object = null)
	{
		$zf2class = 'Zend\Http\Request';
		$sy2class = 'Symfony\Component\HttpFoundation\Request';
		if (is_null($request_object))
		{
			if (!class_exists($sy2class))
			{
				throw DrestException::noRequestObjectDefinedAndCantInstantiateDefaultType($sy2class);
			}
			// Default to using symfony's request object
			$this->adapter = new Adapter\Symfony2(new \Symfony\Component\HttpFoundation\Request());
		} else if (is_object($request_object))
		{
			if ($request_object instanceof $zf2class)
			{
				$this->adapter = new Adapter\ZendFramework2($request_object);
			} elseif ($request_object instanceof $sy2class)
			{
				$this->adapter = new Adapter\Symfony2($request_object);
			} else
			{
				throw DrestException::unknownAdapterForRequestObject($request_object);
			}
		} else
		{
			throw DrestException::invalidRequestObjectPassed();
		}
	}

	/**
	 * Get the adapter object
	 * @return Drest\Request\Adapter\AdapterAbstract $adapter
	 */
	public function getAdapter()
	{
		return $this->adapter;
	}


	/**
	 * Factory call to create a Drest request object
	 * @param mixed $request_object prefered router object
	 */
	public static function create($request_object = null)
	{
		return new self($request_object);
	}

}