<?php
/**
 * This file is part of the Drest package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Lee Davis
 * @copyright Copyright (c) Lee Davis <@leedavis81>
 * @link https://github.com/leedavis81/drest/blob/master/LICENSE
 * @license http://opensource.org/licenses/MIT The MIT X License (MIT)
 */
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

            $resultSet = ResultSet::create(['location' => ($location) ? $location : 'unknown'], 'response');
        } catch (\Exception $e) {
            return $this->handleError($e, Response::STATUS_CODE_500);
        }

        return $resultSet;
    }
}
