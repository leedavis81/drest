<?php
namespace Drest\Tools\Console\Helper;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Helper\Helper;

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
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Retrieves Doctrine ORM EntityManager
     *
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->em;
    }

    /**
     * @see \Symfony\Component\Console\Helper\HelperInterface::getName()
     */
    public function getName()
    {
        return 'entityManager';
    }
}