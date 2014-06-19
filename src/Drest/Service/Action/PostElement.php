<?php
namespace Drest\Service\Action;

use DrestCommon\Response\Response;
use DrestCommon\ResultSet;

class PostElement extends AbstractAction
{

    public function execute()
    {
        $classMetaData = $this->getMatchedRoute()->getClassMetaData();
        $em = $this->getEntityManager();

        $entityClass = $classMetaData->getClassName();
        $object = new $entityClass;

        $this->runHandle($object);
        try {
            $em->persist($object);
            $em->flush($object);

            $this->getResponse()->setStatusCode(Response::STATUS_CODE_201);
            if (($location = $this->getMatchedRoute()->getOriginLocation(
                    $object,
                    $this->getRequest()->getUrl(),
                    $this->getEntityManager()
                )) !== false
            ) {
                $this->getResponse()->setHttpHeader('Location', $location);
            }

            $resultSet = ResultSet::create(array('location' => ($location) ? $location : 'unknown'), 'response');
        } catch (\Exception $e) {
            return $this->handleError($e, Response::STATUS_CODE_500);
        }

        return $resultSet;
    }
}
