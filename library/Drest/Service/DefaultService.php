<?php

namespace Drest\Service;


use Doctrine\ORM,
	Drest\Response,
	Drest\Request,
	Drest\DrestException,
	Drest\Mapping\RouteMetaData;

/**
 *
 * A default service to class - used if AbstractService isn't extended for custom use
 * @author Lee
 *
 */
class DefaultService extends AbstractService
{


	/**
	 * Default method to return a single entity item
	 */
	public function getElement()
	{
	    $classMetaData = $this->matched_route->getClassMetaData();

	    $elementName = $classMetaData->getEntityAlias();
	    $qb = $this->em->createQueryBuilder()->from($classMetaData->getClassName(), $elementName);


	    $qb = $this->addDefaultFields($this->matched_route->getExpose(), $qb, $this->em->getClassMetadata($classMetaData->getClassName()));
	    //$qb = $this->addDefaultJoins($qb, $elementName);


        foreach ($this->matched_route->getRouteParams() as $key => $value)
        {
            $qb->andWhere($elementName . '.' . $key  . ' = :' . $key);
            $qb->setParameter($key, $value);
        }

        try
        {
            return $this->writeData($qb->getQuery()->getSingleResult(ORM\Query::HYDRATE_ARRAY));
        } catch (ORM\ORMException $e)
        {
            echo $e->getMessage();
            echo $e->getTraceAsString(); die;
            if ($e instanceof ORM\NonUniqueResultException)
            {
                $this->response->setStatusCode(Response::STATUS_CODE_300);
            } else
            {
                $this->response->setStatusCode(Response::STATUS_CODE_404);
            }
        }
	}

	public function getCollection()
	{
        $classMetaData = $this->matched_route->getClassMetaData();

        $elementName = $classMetaData->getEntityAlias();
	    $qb = $this->em->createQueryBuilder()->from($classMetaData->getClassName(), $elementName);

	    $qb = $this->addDefaultFields($qb, $elementName);
	    $qb = $this->addDefaultJoins($qb, $elementName);

        foreach ($this->matched_route->getRouteParams() as $key => $value)
        {
            $qb->andWhere($elementName . '.' . $key . ' = :' . $key);
            $qb->setParameter($key, $value);
        }
        try
        {
            return $this->writeData($qb->getQuery()->getResult(ORM\Query::HYDRATE_ARRAY));
        } catch (ORM\ORMException $e)
        {
            echo $e->getMessage();
            echo $e->getTraceAsString(); die;
            /*
            ORM\NonUniqueResultException
            ORM\NoResultException
            ORM\OptimisticLockException
            ORM\PessimisticLockException
            ORM\TransactionRequiredException
            ORM\UnexpectedResultException
            */
            if ($e instanceof ORM\NoResultException)
            {
                $this->response->setStatusCode(Response::STATUS_CODE_204);
            } else
            {
                $this->response->setStatusCode(Response::STATUS_CODE_404);
            }
        }
	}



	protected function addDefaultFields($fields, \Doctrine\ORM\QueryBuilder $qb, \Doctrine\ORM\Mapping\ClassMetadata $classMetaData)
	{

	    $classAlias = $this->getAlias($classMetaData->getName());
	    $ormAssociationMappings = $classMetaData->getAssociationMappings();

	    // Process single fields into a partial set
	    $selectFields = array_filter($fields, function($offset) use ($classMetaData){
	        if (!is_array($offset) && in_array($offset, $classMetaData->getFieldNames()))
	        {
	            return true;
	        }
	        return false;
	    });

        // @todo: keep a reference of the additional id's we added, we need to remove them from the dataset
	    $requiredIdentifiers = array_diff($classMetaData->getIdentifierFieldNames(), $selectFields);
        $qb->addSelect('partial ' . $classAlias . '.{'  . implode(', ', array_merge($selectFields, $requiredIdentifiers)) . '}');


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
                $qb = $this->addDefaultFields($value, $qb, $this->em->getClassMetadata($ormAssociationMappings[$key]['targetEntity']));
	        }
	    }

        return $qb;
	}


	/**
	 * Set the fields the user wants returned to the query builder
	 */
	protected function addDefaultFields2($fields, \Doctrine\ORM\QueryBuilder $qb, \Doctrine\ORM\Mapping\ClassMetadata $classMetaData)
	{
	    // Check for expose field definitions
        foreach ($fields as $key => $value)
        {
            if (is_array($value))
            {
                $ormAssociationMappings = $classMetaData->getAssociationMappings();
                if (isset($ormAssociationMappings[$key]))
                {

                    $qb->leftJoin($this->getAlias($classMetaData->getName()) . '.' . $ormAssociationMappings[$key]['fieldName'], $this->getAlias($ormAssociationMappings[$key]['targetEntity']));
                    $qb = $this->addDefaultFields($value, $qb, $this->em->getClassMetadata($ormAssociationMappings[$key]['targetEntity']));
                }
            } else
            {
                $ormAssociationMappings = $classMetaData->getAssociationMappings();
                //var_dump($ormAssociationMappings); die;
                if (isset($ormAssociationMappings[$value]))
                {
                    // Add it to the join
                    $qb->leftJoin($this->getAlias($classMetaData->getName()) . '.' . $ormAssociationMappings[$value]['fieldName'], $this->getAlias($ormAssociationMappings[$value]['targetEntity']));
                    $qb->addSelect($this->getAlias($classMetaData->getName()) . '.' . $value);
                } else
                {
                    //echo 'adding: ' . $drestMetaData->getEntityAlias() . '.' . $value . PHP_EOL;
                    $qb->addSelect($this->getAlias($classMetaData->getName()) . '.' . $value);
                }
            }
        }
        return $qb;
	}

	/**
	 * Get a unique alias name from an entity class name
	 * @param string $className
	 */
	protected function getAlias($className)
	{
        return strtolower(preg_replace("/[^a-zA-Z0-9_\s]/", "", $className));
	}

	/**
	 *
	 * @todo: Only drops down one tier - this needs to expand to all exposed field definitions
	 * @param \Doctrine\ORM\QueryBuilder $qb
	 * @param unknown_type $rootAlias
	 */
	protected function addDefaultJoins(\Doctrine\ORM\QueryBuilder $qb, $rootAlias)
	{
	    $classMetaData = $this->matched_route->getClassMetaData();
	    foreach ($this->em->getClassMetadata($classMetaData->getClassName())->getAssociationMappings() as $associationMapping)
	    {
	        $alias = strtolower(preg_replace("/[^a-zA-Z0-9_\s]/", "", $associationMapping['targetEntity']));
	        $qb->addSelect($alias);
	        $qb->leftJoin($rootAlias . '.' . $associationMapping['fieldName'], $alias);
	    }
        return $qb;
	}

	public function postElement()
	{
	}

	public function postCollection()
	{
	}

	public function putElement()
	{
	}

	public function putCollection()
	{
	}

	public function deleteElement()
	{
	}

	public function deleteCollection()
	{
	}


	/**
	 * @todo: do we implement this, consider: https://www.owasp.org/index.php/Cross_Site_Tracing
	 * Echo's the clients request directly back to them (no entity data is used)
	 */
	protected function traceRequest()
	{
	}

	protected function optionsRequest()
	{
	}

}