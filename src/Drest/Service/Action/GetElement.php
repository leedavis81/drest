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
            $resultArray = $qb->getQuery()->getSingleResult(ORM\Query::HYDRATE_ARRAY);
            if ($this->getMatchedRoute()->hasHandleCall())
            {
                $className = $this->getMatchedRoute()->getClassMetaData()->getClassName();
                $handleMethod = $this->getMatchedRoute()->getHandleCall();
                $resultArray = $className::$handleMethod($resultArray, $this->getRequest());
            }

            $resultSet = $this->createResultSet($resultArray);
        } catch (\Exception $e) {
            return $this->handleError($e, Response::STATUS_CODE_404);
        }

        return $resultSet;
    }
}
