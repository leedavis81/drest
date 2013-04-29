<?php
namespace Drest\Query;

use Drest\Request,
    Drest\Mapping\RouteMetaData,
    Doctrine\ORM\EntityManager;

class ExposeFields implements \Iterator
{
    /**
     * Current iteration position
     * @var integer $position
     */
    private $position = 0;

    /**
     * Expose fields to be used - multidimensional array
     * @var array $fields
     */
    private $fields;

    /**
     * The matched route
     * @var Drest\Mapping\RouteMetaData $route
     */
    protected $route;

    /**
     * The route expose array - If explicity set it overrides any default config settings
     * @var array $route_expose
     */
    protected $route_expose;

	/**
	 * An array of classes that are registered on default expose depth. Prevents you traversing up and down bi-directional relations
	 * @var array $registered_expose_classes;
	 * @todo: Move this, and the whole processing of it somewhere else
	 */
	protected $registered_expose_classes = array();


	/**
	 * Create an instance of ExposeFields - use create() method
	 * @param RouteMetaData $route - requires a matched route
	 */
	private function __construct(RouteMetaData $route)
	{
        $this->route            = $route;
        $this->route_expose     = $route->getExpose();
	}

	/**
	 * Create an instance of ExposeFields
	 * @param RouteMetaData $route - requires a matched route
	 */
	public static function create(RouteMetaData $route)
	{
        return new self($route);
	}

	/**
	 * Set the default exposure fields using the configured exposure depth
	 * @param EntityManager $em
	 * @param integer $exposureDepth
	 * @param integer $exposureRelationsFetchType
	 * @return Drest\Query\ExposeFields $this object instance
	 */
	public function configureExposeDepth(EntityManager $em, $exposureDepth = 0, $exposureRelationsFetchType = null)
	{
	    if (empty($this->route_expose))
	    {
    	    $this->processExposeDepth(
    	        $this->fields,
    	        $this->route->getClassMetaData()->getClassName(),
    	        $em,
    	        $exposureDepth,
    	        $exposureRelationsFetchType
            );
	    }
        return $this;
	}

	/**
	 * Configure the expose object to filter out fields that have been explicitly requested by the client
	 * @param Request $request
	 * @return Drest\Query\ExposeFields $this object instance
	 */
	public function configureExposureRequest(Request $request)
	{
	    if (empty($this->route_expose))
	    {
	        // Determing the filtered expose using set exposure methods
	        // @todo: pull this from config options / request
            // $this->filterRequestedExpose($requestedExpose, $this->fields);
	    }

        return $this;
	}


	/**
	 * Filter out requested expose fields against what's allowed
	 * @param array $requested - The requested expose definition - invalid / not allowed data is stripped off
	 * @param array $actual - current allowed expose definition
	 */
	protected function filterRequestedExpose(&$requested, &$actual)
	{
	    $actual = (array) $actual;
        foreach ($requested as $requestedKey => $requestedValue)
        {
            if (in_array($requestedValue, $actual))
            {
                continue;
            }

            if (is_array($requestedValue))
            {
                if (isset($actual[$requestedKey]))
                {
                    $this->filterRequestedExpose($requested[$requestedKey], $actual[$requestedKey]);
                    continue;
                }
            } else
            {
                if (isset($actual[$requestedValue]) && is_array($actual[$requestedValue]) && array_key_exists($requestedValue, $actual))
                {
                    continue;
                }
            }
            unset($requested[$requestedKey]);
        }
	}

    /**
     * Recursive function to generate default expose columns
     * @param array $fields - array to be populated recursively (referenced)
     * @param string $class - name of the class to process
     * @param Doctrine\ORM\EntityManager - entity manager used to fetch class information
     * @param integer $depth - maximium depth you want to travel through the relations
     * @param integer|null $fetchType - The required fetch type of the relation
     */
	protected function processExposeDepth(&$fields, $class, EntityManager $em, $depth = 0, $fetchType = null)
	{
        $this->registered_expose_classes[] = $class;
        if ($depth > 0)
        {
            $metaData = $em->getClassMetadata($class);
            $fields = $metaData->getColumnNames();

            if (($depth - 1) > 0)
            {
                --$depth;
                foreach ($metaData->getAssociationMappings() as $key => $assocMapping)
                {
                    if (!in_array($assocMapping['targetEntity'], $this->registered_expose_classes) && (is_null($fetchType) || ($assocMapping['fetch'] == $fetchType)))
                    {
                        $this->processExposeDepth($fields[$key], $assocMapping['targetEntity'], $em, $depth, $fetchType);
                    }
                }
            }
        }
	}


    /**
     * Get the expose fields
     * @return array $fields
     */
    public function toArray()
    {
        if (!empty($this->route_expose))
        {
            return $this->route_expose;
        }
        return $this->fields;
    }

    public function current()
    {
        return $this->fields[$this->position];
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
        return isset($this->fields[$this->position]);
    }

}