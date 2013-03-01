<?php

namespace Drest\Writer;

use Drest\DrestException;

class Xml extends AbstractWriter
{


	/**
	 * @see Drest\Writer\Writer::write()
	 */
	public function write($data, $hydration = \Doctrine\ORM\Query::HYDRATE_ARRAY)
	{
		switch ($hydration)
		{
			case \Doctrine\ORM\Query::HYDRATE_ARRAY:
				if (!is_array($data))
				{
					throw DrestException::writerExpectsArray(get_class($this));
				}
				break;
			case \Doctrine\ORM\Query::HYDRATE_OBJECT:
				break;
			case \Doctrine\ORM\Query::HYDRATE_SCALAR:
				break;
			case \Doctrine\ORM\Query::HYDRATE_SIMPLEOBJECT:
				break;
			case \Doctrine\ORM\Query::HYDRATE_SINGLE_SCALAR:
				break;
		}

	}
}