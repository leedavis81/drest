<?php
namespace Drest\Query;

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
     * Dataset - immuatable and injected on constructs
     * @var array $data
     */
    private $data;

    /**
     * Key name to be used to wrap the resultset in
     * @var string $keyName
     */
    private $keyName;

    /**
     * Construct a result set instance
     * @param array $data
     * @param string $keyName
     */
    private function __construct(array $data, $keyName)
    {
        $keyName = preg_replace("/[^a-zA-Z0-9_\s]/", "", $keyName);
        if (!is_string($keyName))
        {
            DrestException::invalidParentKeyNameForResultSet();
        }
        $this->data = array($keyName => $data);

        $this->position = 0;
    }

    /**
     * Get the resultset
     * @return array $result
     */
    public function toArray()
    {
        return $this->data;
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
     */
    public static function create($data, $keyName)
    {
        return new self($data, $keyName);
    }

}