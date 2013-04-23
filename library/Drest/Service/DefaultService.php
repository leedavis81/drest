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
	protected function getElement()
	{
	    $qb = $this->_em->createQueryBuilder()->select('a')->from($this->getEntityName(), 'a');
        foreach ($this->matched_service->getRouteParams() as $key => $value)
        {
            $qb->andWhere('a.' . $key . ' = :' . $key);
            $qb->setParameter($key, $value);
        }
        try {
            return $qb->getQuery()->getSingleResult(ORM\Query::HYDRATE_ARRAY);
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

	protected function getCollection()
	{
	}

	protected function postElement()
	{
	}

	protected function postCollection()
	{
	}

	protected function putElement()
	{
	}

	protected function putCollection()
	{
	}

	protected function deleteElement()
	{
	}

	protected function deleteCollection()
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