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

	    $qb = $this->registerExpose($this->matched_route->getExpose(), $qb, $this->em->getClassMetadata($classMetaData->getClassName()));
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