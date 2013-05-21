<?php
namespace Drest;


use Drest\Mapping\RouteMetaData,
    Drest\Mapping\ClassMetaData,

    Doctrine\ORM\EntityManager,

    Zend\Code\Generator;

/**
 * Class generator used to create client classes
 * @author Lee
 *
 */
class ClassGenerator
{

    /**
     * Header parameter to look for if a request for class info has be done
     * @var string HEADER_PARAM
     */
    const HEADER_PARAM = 'X-DrestCG';

    /**
     * CG classes generated from routeMetaData
     * @var array $classes - uses className as the key
     */
    protected $classes = array();

    /**
     * The namespace to be used on the generated classes
     * @var string $namespace
     */
    protected $namespace;

    /**
     * Entity manager - required to detect relation types and classNames on expose data
     * @param \Doctrine\ORM\EntityManager $em
     */
    protected $em;

    /**
     * Create an class generator instance
     * @param Doctrine\ORM\EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Create a class generator instance from provided route metadata.
     * Each route will generate it's own unique version of the class (as it will have its own exposure definitions)
     * @param array $classMetaDatas
     * @return array $object - an array of ClassGenerator objects
     */
    public function create(array $classMetaDatas)
    {
        foreach ($classMetaDatas as $classMetaData)
        {
            $expose = array();
            foreach ($classMetaData->getRoutesMetaData() as $routeMetaData)
            {
                $expose = array_merge_recursive($expose, $routeMetaData->getExpose());
            }
            $this->recurseParams($expose, $classMetaData->getClassName());
        }


        // @todo: how do we handle collection routes? - server will return an array of $rootClasses - shouldn't have any effect on the classes we generate
        // $routeMetaData->isCollection();

        serialize($this->classes);
    }

    /**
     * Return the generated classes in serialized form
     * @return string $serilaized
     */
    public function serialize()
    {
        return serialize($this->classes);
    }

    /**
     * Break the class into 2 parts, namespace / classname
     * @param string $className
     * @return array $parts - returns an array with 2 entrys, classname and namespace (respectively)
     */
    protected function getClassParts($className)
    {
        $parts = explode('\\', $className);

        return array(
            array_pop($parts),
            implode('\\', $parts)
        );
    }

    /**
     * Recurse the expose parameters - pass the entities full classname (including namespace)
     * @param array $expose
     * @param string $fullClassName
     */
    protected function recurseParams(array $expose, $fullClassName)
    {
        // get ORM metadata for the current class
        $ormClassMetaData = $this->em->getClassMetadata($fullClassName);

        if (isset($this->classes[$fullClassName]))
        {
            $cg = $this->classes[$fullClassName];
        } else
        {
            $cg = new Generator\ClassGenerator();
            $cg->setName($fullClassName);
        }

        list($className, $namespace) = $this->getClassParts($fullClassName);

        foreach ($expose as $key => $value)
        {
            if (is_array($value))
            {
                $property = new Generator\PropertyGenerator();
                $property->setName($key);
                $property->setVisibility(Generator\AbstractMemberGenerator::FLAG_PUBLIC);

                if ($ormClassMetaData->hasAssociation($key))
                {
                    $assocMapping = $this->handleAssocProperty($property, $ormClassMetaData, $key);
                    if (!$cg->hasProperty($key))
                    {
                        $cg->addProperties(array($property));
                    }

                    $this->recurseParams($value, $assocMapping['targetEntity']);
                }
            } else
            {
                $property = new Generator\PropertyGenerator();
                $property->setName($value);

                if ($ormClassMetaData->hasAssociation($value))
                {
                    // This is an association field with no explicit include fields, include add data field (no relations)
                    $assocMapping = $this->handleAssocProperty($property, $ormClassMetaData, $value);
                    $targetEntity = $assocMapping['targetEntity'];
                    $teCmd = $this->em->getClassMetadata($targetEntity);
                    $this->recurseParams($teCmd->getColumnNames(), $assocMapping['targetEntity']);
                }
                if (!$cg->hasProperty($value))
                {
                    $property->setVisibility(Generator\AbstractMemberGenerator::FLAG_PUBLIC);
                    $cg->addProperties(array($property));
                }
            }
        }

        if (!isset($this->classes[$fullClassName]))
        {
            $this->classes[$fullClassName] = $cg;
        }
    }

    /**
     *
     * Handle an associative property field
     * Updates the property value to correctly reflect the association
     * @param Generator\PropertyGenerator $property
     * @param \Doctrine\ORM\Mapping\ClassMetadata $ormClassMetaData
     * @param string $name - the name of the property
     * @return array the association mapping - if available
     */
    private function handleAssocProperty(Generator\PropertyGenerator &$property, \Doctrine\ORM\Mapping\ClassMetadata $ormClassMetaData, $name)
    {
        $assocMapping = $ormClassMetaData->getAssociationMapping($name);

        if ($assocMapping['type'] & $ormClassMetaData::TO_MANY)
        {
            // This is a collection (should be an Array)
            $property->setDocBlock('@var array $' . $name);
            $property->setDefaultValue(array());
        } else
        {
            // This is a single relation
            $property->setDocBlock('@var ' . $assocMapping['targetEntity'] . ' $' . $name);
        }
        return $assocMapping;
    }
}