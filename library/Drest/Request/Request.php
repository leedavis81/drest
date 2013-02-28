<?php
namespace Drest\Request;

class Request
{


	/**
	 *
	 * Adapter class used for request handling
	 * @var unknown_type
	 */
	protected $adapter;


	public function __construct($request = null)
	{
		if (is_object($request))
		{
			switch (get_class($request))
			{
				case '':
					break;
				case '':
					break;
			}
		}
	}


	public static function create($request = null)
	{
		return new self()
	}

}