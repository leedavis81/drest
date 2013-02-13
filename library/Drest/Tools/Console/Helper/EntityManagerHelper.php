<?php

namespace Drest\Tools\Console\Helper;

use Symfony\Component\Console\Helper\Helper,
    Doctrine\ORM\EntityManager;


/**
 * Doctrine EntityManager Helper
 */
class EntityManagerHelper extends Helper
{

    /**
     * Doctrine ORM EntityManager
     * @var EntityManager
     */
    protected $em;

    /**
     * Constructor
     *
     * @param Doctrine\ORM\EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Retrieves Doctrine ORM EntityManager
     *
     * @return Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->em;
    }

    /**
     * @see Symfony\Component\Console\Helper.HelperInterface::getName()
     */
    public function getName()
    {
        return 'entityManager';
    }

}