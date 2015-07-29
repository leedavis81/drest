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
use DrestCommon\ResultSet;

/**
 *
 * Delete an element action
 * @author Lee
 *
 * Rather than selecting then deleting: We could possibly just delete on supplied parameters
 */
class DeleteElement extends AbstractAction
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

        try {
            $em->remove($object);
            $em->flush();

            $this->getResponse()->setStatusCode(Response::STATUS_CODE_200);

            return ResultSet::create(array('successfully deleted'), 'response');
        } catch (\Exception $e) {
            return $this->handleError($e, Response::STATUS_CODE_500);
        }
    }
}
