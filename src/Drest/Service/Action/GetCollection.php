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

class GetCollection extends AbstractAction
{

    public function execute()
    {
        $classMetaData = $this->getMatchedRoute()->getClassMetaData();
        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder()->from($classMetaData->getClassName(), $classMetaData->getEntityAlias());
        $this->registerExposeFromMetaData($qb, $em->getClassMetadata($classMetaData->getClassName()));

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
