<?php
namespace Drest\Representation;

use Drest\Query\ResultSet,
    Drest\Request,
    Drest\Response;

interface InterfaceRepresentation
{
	/**
	 * Write the results to the $data variable
	 * @param ResultSet $data - The data to be passed into the writer
	 */
	public function write(ResultSet $data);

    /**
     * update the representation to match the data contained within a client data object
     * - This will call the write method that will store its representation in the $data array
     */
	public function update($object);

    /**
     * Content type to be used when this writer is matched
     * @return string content type
     */
    public function getContentType();

    /**
     * Uses configuration options to determine whether this writer instance is the media type expected by the client
     * @param array $configOptions - configuration options for content detection
     * @param Request $request - request object
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

    /**
     * Create an instance of this representation from a given string - $data is loaded up in it's representational form
     * @param string $string
     * @return AbstractRepresentation $representation
     */
    public static function createFromString($string);

	/**
	 * If this object is echo'd print out the contained data
	 * @return string
	 */
	public function __toString();

    /**
     * Get the written output of this data representation
     * @param ResultSet $data
     * @return string $output
     */
    public function output(ResultSet $data);

    /**
     * Get a array representation of the internally loaded data (loaded via write() or createFromString())
     * @param $includeKey - Whether to include the first key in the returned data set
     * @return array $data
     */
    public function toArray($includeKey = true);
}