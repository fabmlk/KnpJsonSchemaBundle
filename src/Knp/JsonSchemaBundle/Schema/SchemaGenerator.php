<?php

namespace Knp\JsonSchemaBundle\Schema;

use Knp\JsonSchemaBundle\Model\Property;
use Knp\JsonSchemaBundle\Model\PropertyFactory;
use Knp\JsonSchemaBundle\Model\Schema;
use Knp\JsonSchemaBundle\Model\SchemaFactory;
use Knp\JsonSchemaBundle\Property\PropertyHandlerInterface;
use Knp\JsonSchemaBundle\Reflection\ReflectionFactory;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SchemaGenerator
{
    protected $reflectionFactory;
    protected $schemaRegistry;
    protected $schemaFactory;
    protected $propertyFactory;
    protected $propertyHandlers;
    protected $aliases = array();
    protected $defaultOptions = array();

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        ReflectionFactory $reflectionFactory,
        SchemaRegistry $schemaRegistry,
        SchemaFactory $schemaFactory,
        PropertyFactory $propertyFactory
    ) {
        $this->urlGenerator      = $urlGenerator;
        $this->reflectionFactory = $reflectionFactory;
        $this->schemaRegistry    = $schemaRegistry;
        $this->schemaFactory     = $schemaFactory;
        $this->propertyFactory   = $propertyFactory;
        $this->propertyHandlers  = new \SplPriorityQueue;
        $this->defaultOptions = array(
            'version' => Schema::SCHEMA_V7,
            'id' => function ($alias) {
                return $this->urlGenerator->generate('show_json_schema', array('alias' => $alias), true) . '#';
            },
            'groups' => array(),
            'additionalProperties' => true
        );
    }

    public function generate($alias, $options = array())
    {
        $this->aliases[] = $alias;
        $options = array_merge($this->defaultOptions, $options);

        $className = $this->schemaRegistry->getNamespace($alias);
        $refl      = $this->reflectionFactory->create($className);
        $schema    = $this->schemaFactory->createSchema($alias);

        $schema->setId(is_callable($options['id']) ? $options['id']($alias) : (string) $options['id']);
        $schema->setSchema($options['version']);
        $schema->setType(Schema::TYPE_OBJECT);
        $schema->setGroups($options['groups']);
        $schema->setAdditionalProperties($options['additionalProperties']);

        foreach ($refl->getProperties() as $property) {
            $property = $this->propertyFactory->createProperty($property->name);
            $this->applyPropertyHandlers($className, $property);

            if (!$property->isIgnored() && $property->hasType(Property::TYPE_OBJECT) && $property->getObject()) {
                // Make sure that we're not creating a reference to the parent schema of the property
                if (!in_array($property->getObject(), $this->aliases)) {
                    $property->setSchema(
                        $this->generate($property->getObject())
                    );
                } else {
                    $property->setIgnored(true);
                }
            }

            if (!$property->isIgnored()) {
                $schema->addProperty($property);
            }
        }

        return $schema;
    }

    public function registerPropertyHandler(PropertyHandlerInterface $handler, $priority)
    {
        $this->propertyHandlers->insert($handler, $priority);
    }

    public function getPropertyHandlers()
    {
        return array_values(iterator_to_array(clone $this->propertyHandlers));
    }

    private function applyPropertyHandlers($className, Property $property)
    {
        $propertyHandlers = clone $this->propertyHandlers;

        while ($propertyHandlers->valid()) {
            $handler = $propertyHandlers->current();

            $handler->handle($className, $property);

            $propertyHandlers->next();
        }
    }
}
