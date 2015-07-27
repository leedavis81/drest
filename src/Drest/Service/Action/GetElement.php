<?php
namespace Drest\Service\Action;

use Doctrine\ORM;
use DrestCommon\Response\Response;

class GetElement extends AbstractAction
{

    public function execute()
    {
        $classMetaData = $this->getMatchedRoute()->getClassMetaData();
        $elementName = $classMetaData->getEntityAlias();

        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder()->from($classMetaData->getClassName(), $elementName);
        $this->registerExposeFromMetaData($qb, $em->getClassMetadata($classMetaData->getClassName()));

        foreach ($this->getMatchedRoute()->getRouteParams() as $key => $value) {
            $qb->andWhere($elementName . '.' . $key . ' = :' . $key);
            $qb->setParameter($key, $value);
        }

        try {
            $resultSet = $this->createResultSet($qb->getQuery()->getSingleResult(ORM\Query::HYDRATE_ARRAY));
        } catch (\Exception $e) {
            return $this->handleError($e, Response::STATUS_CODE_404);
        }

        return $resultSet;
    }
}
