<?php

namespace Drest\Service;


use Drest\Query\ResultSet;

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

        foreach ($this->matched_route->getRouteParams() as $key => $value)
        {
            $qb->andWhere($elementName . '.' . $key  . ' = :' . $key);
            $qb->setParameter($key, $value);
        }

        try
        {
            $resultSet = $this->createResultSet($qb->getQuery()->getSingleResult(ORM\Query::HYDRATE_ARRAY));
        } catch (\Exception $e)
        {
            return $this->handleError($e, Response::STATUS_CODE_404);
        }

        $this->renderDeterminedRepresentation($resultSet);
	}

	/**
	 *
	 * Default method to return a collection of a certain entity type
	 */
	public function getCollection()
	{
        $classMetaData = $this->matched_route->getClassMetaData();
        $elementName = $classMetaData->getEntityAlias();

	    $qb = $this->registerExpose(
	        $this->matched_route->getExpose(),
	        $this->em->createQueryBuilder()->from($classMetaData->getClassName(), $elementName),
	        $this->em->getClassMetadata($classMetaData->getClassName())
        );

        foreach ($this->matched_route->getRouteParams() as $key => $value)
        {
            $qb->andWhere($elementName . '.' . $key . ' = :' . $key);
            $qb->setParameter($key, $value);
        }
        try
        {
            $resultSet = $this->createResultSet($qb->getQuery()->getResult(ORM\Query::HYDRATE_ARRAY));
        } catch (\Exception $e)
        {
            return $this->handleError($e, Response::STATUS_CODE_404);
        }

        $this->renderDeterminedRepresentation($resultSet);
	}


	/**
	 * Method to post a new entity
	 */
	public function postElement()
	{
        $classMetaData = $this->matched_route->getClassMetaData();

        $entityClass = $classMetaData->getClassName();
        $object = new $entityClass;

        // Run any attached handle function
        if ($this->matched_route->hasHandleCall())
        {
            $handleMethod = $this->matched_route->getHandleCall();
            $object->$handleMethod($this->representation->toArray(false));
        }

        try
        {
            $this->em->persist($object);
            $this->em->flush($object);

            $this->response->setStatusCode(Response::STATUS_CODE_201);
            if (($location = $this->matched_route->getOriginLocation($object, $this->request->getUrl())) !== false)
            {
                $this->response->setHttpHeader('Location', $location);
            }

            $resultSet = ResultSet::create(array('location' => ($location) ? $location : 'unknown'), 'response');
        } catch (\Exception $e)
        {
            return $this->handleError($e, Response::STATUS_CODE_500);
        }

        $this->renderDeterminedRepresentation($resultSet);
	}


	/**
	 * Method to update an existing entity
	 */
	public function putElement()
	{
        $classMetaData = $this->matched_route->getClassMetaData();
	    $elementName = $classMetaData->getEntityAlias();

	    $qb = $this->em->createQueryBuilder()->select($elementName)->from($classMetaData->getClassName(), $elementName);
        foreach ($this->matched_route->getRouteParams() as $key => $value)
        {
            $qb->andWhere($elementName . '.' . $key  . ' = :' . $key);
            $qb->setParameter($key, $value);
        }

        try
        {
            $object = $qb->getQuery()->getSingleResult(ORM\Query::HYDRATE_OBJECT);
        } catch (\Exception $e)
        {
            return $this->handleError($e, Response::STATUS_CODE_404);
        }

	    // Run any attached handle function
        if ($this->matched_route->hasHandleCall())
        {
            $handleMethod = $this->matched_route->getHandleCall();
            $object->$handleMethod($this->representation->toArray(false));
        }

        // Attempt to save the modified resource
        try
        {
            $this->em->persist($object);
            $this->em->flush($object);

            $location = $this->matched_route->getOriginLocation($object, $this->request->getUrl());
            $this->response->setStatusCode(Response::STATUS_CODE_200);
            $resultSet = ResultSet::create(array('location' => ($location) ? $location : 'unknown'), 'response');
        } catch (\Exception $e)
        {
            return $this->handleError($e, Response::STATUS_CODE_500);
        }

        $this->renderDeterminedRepresentation($resultSet);
	}

	/**
	 * Method to part update an existing entity (should be handled exactly the same way as a PUT)
	 */
	public function patchElement()
	{
	    $this->putElement();
	}


	public function deleteElement()
	{
	}

	public function deleteCollection()
	{
	}

	/**
	 * @todo: Add a default trace request action. Do we implement this?, consider: https://www.owasp.org/index.php/Cross_Site_Tracing
	 * Echo's the clients request directly back to them (no entity data is used)
	 */
}