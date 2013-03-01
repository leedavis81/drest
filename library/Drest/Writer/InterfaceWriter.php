<?php

namespace Drest\Writer;

interface InterfaceWriter
{

	/**
	 * Write the data out in a new format
	 * @param mixed $data - The data to be passed into the writer
	 * @param integer $hydration - The hydration mechanism used to fetch the data can be:
	 * \Doctrine\ORM\Query::HYDRATE_ARRAY,
	 * \Doctrine\ORM\Query::HYDRATE_OBJECT
	 * \Doctrine\ORM\Query::HYDRATE_SCALAR,
	 * \Doctrine\ORM\Query::HYDRATE_SIMPLEOBJECT,
	 * \Doctrine\ORM\Query::HYDRATE_SINGLE_SCALAR
	 */
	public function write($data, $hydration);

}