<?php
namespace Drest\Service\Action;

use Doctrine\ORM;
use DrestCommon\Response\Response;

class GetCollection extends AbstractAction
{

    public function execute()
    {
        $classMetaData = $this->getMatchedRoute()->getClassMetaData();
        $em = $this->getEntityManager();

        $qb = $this->registerExpose(
            $this->getMatchedRoute()->getExpose(),
            $em->createQueryBuilder()->from($classMetaData->getClassName(), $classMetaData->getEntityAlias()),
            $em->getClassMetadata($classMetaData->getClassName())
        );

        foreach ($this->getMatchedRoute()->getRouteParams() as $key => $value) {
            $qb->andWhere($classMetaData->getEntityAlias() . '.' . $key . ' = :' . $key);
            $qb->setParameter($key, $value);
        }

        try {
            $resultSet = $this->createResultSet($qb->getQuery()->getResult(ORM\Query::HYDRATE_ARRAY));
        } catch (\Exception $e) {
            return $this->handleError($e, Response::STATUS_CODE_404);
        }

        return $resultSet;
    }
}