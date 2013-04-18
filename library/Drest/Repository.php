<?php

namespace Drest;

use Doctrine\ORM,
	Drest\Repository\DefaultRepository,
	Drest\Mapping\ServiceMetaData;

class Repository extends ORM\EntityRepository
{

	/**
	 * Drest request object
	 * @var \Drest\Request $request
	 */
	protected $request;

	/**
	 * Drest response object
	 * @var \Drest\Response $response
	 */
	protected $response;

	/**
	 * When a service object is matched, it's injected into the repository class
	 * @var Drest\Mapping\ServiceMetaData $service
	 */
	protected $matched_service;

	/**
	 * Inspects the request object and runs the default request function based on the entity type and verbs used
	 * @return array $data
	 */
	public function executeDefaultMethod()
	{
        if (!$this->request instanceof Request)
        {
            throw DrestException::repositoryNeedsRequestObject();
        }

        switch ($this->matched_service->getContentType())
        {
            case ServiceMetaData::CONTENT_TYPE_ELEMENT:
                switch ($this->request->getHttpMethod())
                {
                    case Request::METHOD_GET:
                        return $this->defaultGetItem();
                        break;
                }
                break;
            case ServiceMetaData::CONTENT_TYPE_COLLECTION:
                break;
        }
	}

	/**
	 * Inject the request object into the repository
	 * @param Drest\Request $request
	 */
	public function setRequest(Request $request)
	{
	    $this->request = $request;
	}

	/**
	 * Inject the response object into the repository
	 * @param Drest\Response $response
	 */
	public function setResponse(Response $response)
	{
	    $this->response = $response;
	}

	/**
	 * Set the matched service object
	 * @param Drest\Mapping\ServiceMetaData $matched_service
	 */
	public function setMatchedService(ServiceMetaData $matched_service)
	{
        $this->matched_service = $matched_service;
	}

	/**
	 * Get the service object that was matched
	 * @return Drest\Mapping\ServiceMetaData $matched_service
	 */
	public function getMatchedService()
	{
	    return $this->matched_service;
	}


	/**
	 * Default method to return a single entity item
	 */
	protected function defaultGetItem()
	{
	    $qb = $this->_em->createQueryBuilder()->select('a')->from($this->getEntityName(), 'a');
        foreach ($this->matched_service->getParams() as $key => $value)
        {
            $qb->andWhere('a.' . $key . ' = :' . $key);
            $qb->setParameter($key, $value);
        }
        try {
            return $qb->getQuery()->getSingleResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
        } catch (ORM\ORMException $e)
        {
            if ($e instanceof ORM\NonUniqueResultException)
            {
                $this->response->setStatusCode(Response::STATUS_CODE_300);
            } else
            {
                $this->response->setStatusCode(Response::STATUS_CODE_404);
            }
        }
	}

	protected function defaultGetCollection()
	{
	}

	protected function defaultPostItem()
	{
	}

	protected function defaultPostCollection()
	{
	}

	protected function defaultPutItem()
	{
	}

	protected function defaultPutCollection()
	{
	}

	protected function defaultDeleteItem()
	{
	}

	protected function defaultDeleteCollection()
	{
	}


	/**
	 * @todo: do we implement this, consider: https://www.owasp.org/index.php/Cross_Site_Tracing
	 * Echo's the clients request directly back to them (no entity data is used)
	 */
	protected function defaultTraceRequestQuery()
	{
	}

}