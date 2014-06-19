<?php
namespace Drest\Service\Action;

use Doctrine\ORM;
use DrestCommon\Response\Response;
use DrestCommon\ResultSet;

class PatchElement extends AbstractAction
{

    public function execute()
    {
        $matchedRoute = $this->getMatchedRoute();
        $classMetaData = $matchedRoute->getClassMetaData();
        $elementName = $classMetaData->getEntityAlias();

        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder()->select($elementName)->from($classMetaData->getClassName(), $elementName);
        foreach ($matchedRoute->getRouteParams() as $key => $value) {
            $qb->andWhere($elementName . '.' . $key . ' = :' . $key);
            $qb->setParameter($key, $value);
        }

        try {
            $object = $qb->getQuery()->getSingleResult(ORM\Query::HYDRATE_OBJECT);
        } catch (\Exception $e) {
            return $this->handleError($e, Response::STATUS_CODE_404);
        }

        $this->runHandle($object);

        // Attempt to save the modified resource
        try {
            $em->persist($object);
            $em->flush($object);

            $location = $matchedRoute->getOriginLocation(
                $object,
                $this->getRequest()->getUrl(),
                $this->getEntityManager()
            );
            $this->getResponse()->setStatusCode(Response::STATUS_CODE_200);
            $resultSet = ResultSet::create(array('location' => ($location) ? $location : 'unknown'), 'response');
        } catch (\Exception $e) {
            return $this->handleError($e, Response::STATUS_CODE_500);
        }

        return $resultSet;
    }
}
