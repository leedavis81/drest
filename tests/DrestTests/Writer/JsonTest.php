<?php

namespace DrestTests\Writer;

use Drest\Writer;

class JsonTest extends WriterTestCase
{

	public function testWriterParsesDataFetchedFromHydrateArray()
	{
		$writer = new Writer\Json();


		$response = $writer->write($data, \Doctrine\ORM\Query::HYDRATE_ARRAY);

		$this->assertEquals('13', '13');

		$this->assertJsonStringEqualsJsonString('{}', $response);
	}
}




