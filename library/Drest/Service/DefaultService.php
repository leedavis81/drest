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
	    $qb = $this->em->createQueryBuilder()->select('a')->from($classMetaData->getClassName(), 'a');
        foreach ($this->matched_route->getRouteParams() as $key => $value)
        {
            $qb->andWhere('a.' . $key . ' = :' . $key);
            $qb->setParameter($key, $value);
        }
        try
        {
            // @todo: run a helper untility to autowrap this stuff for us
            return array($classMetaData->getElementName() => $qb->getQuery()->getSingleResult(ORM\Query::HYDRATE_ARRAY));
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
	}

	public function getCollection()
	{
        $classMetaData = $this->matched_route->getClassMetaData();
	    $qb = $this->em->createQueryBuilder()->select('a')->from($classMetaData->getClassName(), 'a');
        foreach ($this->matched_route->getRouteParams() as $key => $value)
        {
            $qb->andWhere('a.' . $key . ' = :' . $key);
            $qb->setParameter($key, $value);
        }
        try
        {
            return $this->wrapResults($qb->getQuery()->getResult(ORM\Query::HYDRATE_ARRAY));
        } catch (ORM\ORMException $e)
        {
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