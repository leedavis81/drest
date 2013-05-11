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

        $qb = $this->registerExpose(
	        $this->matched_route->getExpose(),
	        $this->em->createQueryBuilder()->from($classMetaData->getClassName(), $elementName),
	        $this->em->getClassMetadata($classMetaData->getClassName())
        );

        // No data has been exposed
        // @todo: tidy - this can be moved into the setup call on abstract service. If no expose fields are present, fail early (for all GET requests)
        if (sizeof($qb->getDQLPart('select')) === 0)
        {
            $this->renderDeterminedWriter($this->createResultSet(array()));
            return;
        }

        foreach ($this->matched_route->getRouteParams() as $key => $value)
        {
            $qb->andWhere($elementName . '.' . $key  . ' = :' . $key);
            $qb->setParameter($key, $value);
        }

        try
        {
            $resultSet = $this->createResultSet($qb->getQuery()->getSingleResult(ORM\Query::HYDRATE_ARRAY));
        } catch (ORM\ORMException $e)
        {
            if ($e instanceof ORM\NonUniqueResultException)
            {
                $this->response->setStatusCode(Response::STATUS_CODE_300);
            } else
            {
                $this->response->setStatusCode(Response::STATUS_CODE_404);
            }
        }

        $this->renderDeterminedWriter($resultSet);
	}

	public function getCollection()
	{
        $classMetaData = $this->matched_route->getClassMetaData();
        $elementName = $classMetaData->getEntityAlias();

	    $qb = $this->registerExpose(
	        $this->matched_route->getExpose(),
	        $this->em->createQueryBuilder()->from($classMetaData->getClassName(), $elementName),
	        $this->em->getClassMetadata($classMetaData->getClassName())
        );

        // No data has been exposed
        // @todo: tidy
        if (sizeof($qb->getDQLPart('select')) === 0)
        {
            $this->renderDeterminedWriter($this->createResultSet(array()));
            return;
        }

        foreach ($this->matched_route->getRouteParams() as $key => $value)
        {
            $qb->andWhere($elementName . '.' . $key . ' = :' . $key);
            $qb->setParameter($key, $value);
        }
        try
        {
            $this->renderDeterminedWriter($this->createResultSet($qb->getQuery()->getResult(ORM\Query::HYDRATE_ARRAY)));
        } catch (ORM\ORMException $e)
        {
            echo $e->getMessage();
            echo $e->getTraceAsString();
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