<?php
namespace Drest\Repository;

use \Doctrine\ORM\Query;
/**
 *
 * Class containing default repository calls based on HTTP verb used
 * @author Lee
 *
 */
class DefaultRepository
{

	public static function getItem(Drest\Repository $repository)
	{
	    $repository->_em->createQuery('SELECT FROM ' . $repository->getCollection())
	    $repository->findBy($criteria)
        $qb = $repository->createQueryBuilder($alias);
        $qb->getQuery()->execute(null, Query::HYDRATE_ARRAY)




        $query = $this->_em->createQuery('SELECT COUNT(sr.id) as total FROM ' . $mapper->getServiceRecordEntityClass() . ' sr WHERE sr.data_import_flag != :data_import_flag AND sr.program = :program_id');
        $query->setParameters(array(
        	'data_import_flag' => MappedServiceRecordRepository::IMPORT_FLAG_NOT_PROCESSING,
        	'program_id' => $program->getId(),
        ));
        try
        {
            $res = $query->getSingleResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
        } catch(\Exception $e)
        {
            echo $e->getMessage();
            echo $e->getTraceAsString();
        }

	}

	public static function getCollection(Drest\Repository $repository)
	{

	}

	public static function putItem()
	{

	}

	public static function putCollection()
	{

	}

	public static function deleteItem()
	{

	}

	public static function deleteCollection()
	{

	}

	public static function postItem()
	{

	}

	public static function postCollection()
	{

	}

}