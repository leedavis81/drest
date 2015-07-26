<?php
namespace Drest;

use Doctrine\Common\Persistence\AbstractManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
/**
 * Drest EntityManagerRegistry
 */
class EntityManagerRegistry extends AbstractManagerRegistry
{
    /**
     * Simple array container
     * @var array
     */
    protected $container;

    /**
     * Fetches/creates the given services.
     *
     * A service in this context is connection or a manager instance.
     *
     * @param string $name The name of the service.
     * @return object The instance of the given service.
     */
    protected function getService($name)
    {
        if (!isset($this->container[$name]))
        {
            throw new \InvalidArgumentException(sprintf('Service named "%s" does not exist.', $name));
        }
        return $this->container[$name];
    }

    /**
     * Resets the given services.
     *
     * A service in this context is connection or a manager instance.
     *
     * @param string $name The name of the service.
     * @return void
     */
    protected function resetService($name)
    {
        $this->container[$name] = null;
    }

    /**
     * Resolves a registered namespace alias to the full namespace.
     *
     * This method looks for the alias in all registered object managers.
     *
     * @param string $alias The alias.
     * @return string The full namespace.
     * @throws ORMException
     */
    public function getAliasNamespace($alias)
    {
        foreach (array_keys($this->getManagers()) as $name) {
            try {
                return $this->getManager($name)->getConfiguration()->getEntityNamespace($alias);
            } catch (ORMException $e) {
            }
        }
        throw ORMException::unknownEntityNamespace($alias);
    }


    /**
     * Set the service container
     * @param $container
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }

    /**
     * Get a simple manager registry if you only use one $em
     * It's advised you either extend or implement your own version of AbstractManagerRegistry
     * for custom handling of varied services
     * @param EntityManager $em
     * @return EntityManagerRegistry
     */
    public static function getSimpleManagerRegistry(EntityManager $em)
    {
        $registry = new self(
            'drestApp',
            array(),
            array('defaultManager' => 'default'),
            null,
            'defaultManager',
            '\Doctrine\ORM\Proxy\Proxy'
        );
        $registry->setContainer(array('default' => $em));
        return $registry;
    }

}