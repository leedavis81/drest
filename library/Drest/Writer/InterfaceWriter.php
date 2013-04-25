<?php
namespace Drest\Writer;

use Drest\Mapping\RouteMetaData;

interface InterfaceWriter
{

    /**
     * construct an instance of a writer by passing in the matched route metadata
     * @param Drest\Mapping\RouteMetaData $routeMetaData
     */
    public function __construct(RouteMetaData $routeMetaData);

	/**
	 * Write the data out in the required format
	 * @param mixed $data - The data to be passed into the writer
	 */
	public function write($data);

	/**
	 * Return an array of applicable accept header values that should match this writer
	 * @return array
	 */
	public function getMatchableAcceptHeaders();

	/**
	 * Return an array of applicable extension valuies that should match this writer. eg 'json', 'jsn'
	 * Exclude the extension dot
	 * return array
	 */
	public function getMatchableExtensions();

	/**
	 * Return an array of acceptable value on the format param that should match this writer
	 * @return array
	 */
	public function getMatchableFormatParams();
}