<?php
namespace Drest\Query;

use Drest\DrestException;

/**
 * Drest result set
 * @author Lee
 */
class ResultSet implements \Iterator
{
    /**
     * Current iteration position
     * @var integer $position
     */
    private $position = 0;

    /**
     * Data - immutable and injected on construction
     * @var array $data
     */
    private $data;

    /**
     * Key name to be used to wrap the result set in
     * @var string $keyName
     */
    private $keyName;

    /**
     * Construct a result set instance
     * @param array $data
     * @param string $keyName
     * @throws DrestException
     */
    private function __construct(array $data, $keyName)
    {
        $keyName = preg_replace("/[^a-zA-Z0-9_\s]/", "", $keyName);
        if (!is_string($keyName)) {
            throw DrestException::invalidParentKeyNameForResults();
        }
        $this->data = $data;
        $this->keyName = $keyName;

        $this->position = 0;
    }

    /**
     * Get the result set
     * @return array $result
     */
    public function toArray()
    {
        return array($this->keyName => $this->data);
    }

    public function current()
    {
        return $this->data[$this->position];
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        ++$this->position;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function valid()
    {
        return isset($this->data[$this->position]);
    }

    /**
     * Create an instance of a results set object
     * @param array $data
     * @param string $keyName
     * @return ResultSet
     */
    public static function create($data, $keyName)
    {
        return new self($data, $keyName);
    }
}