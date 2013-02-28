<?php

namespace Drest\Writer;

class Json extends AbstractWriter
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
					throw new \Drest\DrestException::writerExpectsArray();
				}
				break;
		}

	}
}