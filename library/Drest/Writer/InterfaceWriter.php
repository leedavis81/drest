<?php
namespace Drest\Writer;

use Drest\Mapping\RouteMetaData,
    Drest\Request;

interface InterfaceWriter
{

	/**
	 * Write the data out in the required format
	 * @param mixed $data - The data to be passed into the writer
	 */
	public function write($data);

    /**
     * Content type to be used when this writer is matched
     * @return string content type
     */
    public function getContentType();

    /**
     * Uses configuration options to determine whether this writer instance is the media type expected by the client
     * @param array $configOptions - configuration options for content detection
     * @param Drest\Request $request - request object
     * @return boolean $result
     */
    public function isExpectedContent(array $configOptions, Request $request);

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