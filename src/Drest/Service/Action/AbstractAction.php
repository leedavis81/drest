<?php
namespace Drest\Service\Action;

use Drest\Service,
    Drest\Query\ResultSet;

/**
 * abstract action class.
 * This should be extended for creating and registering custom service actions
 * @author Lee
 *
 */
abstract class AbstractAction
{
    /**
     * The service class this action is registered to
     * @var Drest\Service $service
     */
    protected $service;

    /**
     * addional key fields that are included in partial queries to make the DQL valid
     * These columns should be purged from the result set
     * @var array $addedKeyFields
     */
    protected $addedKeyFields;

    /**
     * Create an instance of this action - requires the owning service object
     * @param Drest\Service $service
     */
    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    /**
     * Execute the action
     * If a response body is required it should return a result set object
     * @return null|ResultSet $resultSet
     */
    abstract public function execute();

    /**
     * Get the service class this action is registered against
     * @return Drest\Service
     */
    protected function getService()
    {
        return $this->service;
    }

    /**
     * get the matched route object
     * @return Drest\Mapping\RouteMetaData $route
     */
    protected function getMatchedRoute()
    {
        return $this->service->getMatchedRoute();
    }

    /**
     * Get the entity manager from the service object
     * @return \Doctrine\ORM\EntityManager $em
     */
    protected function getEntityManager()
    {
        return $this->service->getEntityManager();
    }

    /**
     * Get the response object
     * @return Drest\Response $response
     */
    protected function getResponse()
    {
        return $this->service->getResponse();
    }

	/**
	 * Get the request object
	 * @return Drest\Request $request
	 */
    protected function getRequest()
    {
        return $this->service->getRequest();
    }

	/**
	 * Get the predetermined representation
	 * @param Representation\AbstractRepresentation $representation
	 */
	public function getRepresentation()
	{
	    return $this->service->getRepresentation();
	}

	/**
	 * Handle an error - set the resulting error document to the response object
	 * @param \Exception $e
  	 * @param $defaultResponseCode the default response code to use if no match on exception type occurs
  	 * @param Drest\Error\Response\ResponseInterface $errorDocument
  	 * @return ResultSet the error result set
	 */
	public function handleError(\Exception $e, $defaultResponseCode = 500, Drest\Error\Response\ResponseInterface $errorDocument = null)
	{
	    return $this->service->handleError($e, $defaultResponseCode, $errorDocument);
	}

    /**
     * A recursive function to process the specified expose fields for a fetch request (GET)
     * @param array $fields - expose fields to process
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param \Doctrine\ORM\Mapping\ClassMetadata $classMetaData
	 * @param string $key - The key of the expose entry being processed
     * @param array $addedKeyFields
     */
	protected function registerExpose($fields, \Doctrine\ORM\QueryBuilder $qb, \Doctrine\ORM\Mapping\ClassMetadata $classMetaData, &$addedKeyFields = array(), $key = null)
	{
	    if (empty($fields))
	    {
	        return $qb;
	    }

	    $addedKeyFields = (array) $addedKeyFields;
	    $classAlias = $this->getAlias($classMetaData->getName());
	    $ormAssociationMappings = $classMetaData->getAssociationMappings();

	    // Process single fields into a partial set - Filter fields not avialble on class meta data
	    $selectFields = array_filter($fields, function($offset) use ($classMetaData){
	        if (!is_array($offset) && in_array($offset, $classMetaData->getFieldNames()))
	        {
	            return true;
	        }
	        return false;
	    });

	    // merge required identifier fields with select fields
	    $keyFieldDiff = array_diff($classMetaData->getIdentifierFieldNames(), $selectFields);
	    if (!empty($keyFieldDiff))
	    {
            $addedKeyFields = $keyFieldDiff;
	        $selectFields = array_merge($selectFields, $keyFieldDiff);
	    }

	    if (!empty($selectFields))
	    {
            $qb->addSelect('partial ' . $classAlias . '.{'  . implode(', ', $selectFields) . '}');
	    }

	    // Process relational field with no deeper expose restrictions
	    $relationalFields = array_filter($fields, function($offset) use ($classMetaData) {
            if (!is_array($offset) && in_array($offset, $classMetaData->getAssociationNames()))
	        {
	            return true;
	        }
	        return false;
	    });

	    foreach ($relationalFields as $relationalField)
	    {
            $qb->leftJoin($classAlias . '.' . $relationalField, $this->getAlias($ormAssociationMappings[$relationalField]['targetEntity']));
	        $qb->addSelect($this->getAlias($ormAssociationMappings[$relationalField]['targetEntity']));
	    }

	    foreach ($fields as $key => $value)
	    {
	        if (is_array($value) && isset($ormAssociationMappings[$key]))
	        {
	            $qb->leftJoin($classAlias . '.' . $key, $this->getAlias($ormAssociationMappings[$key]['targetEntity']));
                $qb = $this->registerExpose($value, $qb, $this->getEntityManager()->getClassMetadata($ormAssociationMappings[$key]['targetEntity']), $addedKeyFields[$key], $key);
	        }
	    }

	    $this->addedKeyFields = $addedKeyFields;
        return $qb;
	}

	/**
	 * Method used to write to the $data aray.
	 * - 	wraps results in a single entry array keyed by entity name.
	 * 		Eg array(user1, user2) becomes array('users' => array(user1, user2)) - this is useful for a more descriptive output of collection resources
	 * - 	Removes any addition expose fields required for a partial DQL query
	 * @param array $data - the data fetched from the database
	 * @param string $keyName - the key name to use to wrap the data in. If null will attempt to pluralise the entity name on collection request, or singulise on single element request
	 * @return Drest\Query\ResultSet $data
	 */
	protected function createResultSet(array $data, $keyName = null)
	{
	    $matchedRoute = $this->getMatchedRoute();
	    $classMetaData = $matchedRoute->getClassMetaData();

	    // Recursively remove any additionally added pk fields ($data must be a single record hierarchy. Iterate if we're getting a collection)
	    if ($matchedRoute->isCollection())
	    {
            for ($x = 0; $x < sizeof($data); $x++)
            {
                $this->removeAddedKeyFields($this->addedKeyFields, $data[$x]);
            }
	    } else
	    {
	        $this->removeAddedKeyFields($this->addedKeyFields, $data);
	    }

        if (is_null($keyName))
        {
	        reset($data);
            if (sizeof($data) === 1 && is_string(key($data)))
            {
                // Use the single keyed array as the result set key
                 $keyName = key($data);
                 $data = $data[key($data)];
            } else
            {
                $keyName = ($matchedRoute->isCollection()) ? $classMetaData->getCollectionName() : $classMetaData->getElementName();
            }
        }

	    return ResultSet::create($data, $keyName);
	}


	/**
	 * Functional recursive method to remove any fields added to make the partial DQL work and remove the data
	 * @param array $addedKeyFields
	 * @param array $data - pass by reference
	 */
	protected function removeAddedKeyFields($addedKeyFields, &$data)
	{
	    $addedKeyFields = (array) $addedKeyFields;
	    foreach ($data as $key => $value)
	    {
            if (is_array($value) && isset($addedKeyFields[$key]))
            {
                if (is_int($key))
                {
                    for ($x = 0; $x <= sizeof($value); $x++)
                    {
                        if (isset($data[$x]) && is_array($data[$x]))
                        {
                            $this->removeAddedKeyFields($addedKeyFields[$key], $data[$x]);
                        }
                    }
                } else
                {
                    $this->removeAddedKeyFields($addedKeyFields[$key], $data[$key]);
                }

            } else
            {
                if (is_array($addedKeyFields) && in_array($key, $addedKeyFields))
                {
                    unset($data[$key]);
                }
            }
	    }
	    return $data;
	}


	/**
	 * Run the handle call on an entity object
	 * @param object $object
	 */
	protected function runHandle($object)
	{
	    $matchedRoute = $this->getMatchedRoute();
        // Run any attached handle function
        if ($matchedRoute->hasHandleCall())
        {
            $handleMethod = $matchedRoute->getHandleCall();
            $object->$handleMethod($this->getRepresentation()->toArray(false));
        }
	}

	/**
	 * Get a unique alias name from an entity class name
	 * @param string $className
	 */
	protected function getAlias($className)
	{
        return strtolower(preg_replace("/[^a-zA-Z0-9_\s]/", "", $className));
	}
}