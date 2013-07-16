<?php
namespace Drest\Service\Action;

use DrestCommon\ResultSet;
use DrestCommon\Response\Response;

class DeleteCollection extends AbstractAction
{
    public function execute()
    {
        $matchedRoute = $this->getMatchedRoute();
        $classMetaData = $matchedRoute->getClassMetaData();
        $elementName = $classMetaData->getEntityAlias();

        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder()->delete($classMetaData->getClassName(), $elementName);
        foreach ($matchedRoute->getRouteParams() as $key => $value) {
            $qb->andWhere($elementName . '.' . $key . ' = :' . $key);
            $qb->setParameter($key, $value);
        }

        try {
            $qb->getQuery()->execute();
            $this->getResponse()->setStatusCode(Response::STATUS_CODE_200);
            return ResultSet::create(array('successfully deleted'), 'response');
        } catch (\Exception $e) {
            return $this->handleError($e, Response::STATUS_CODE_500);
        }
    }
}