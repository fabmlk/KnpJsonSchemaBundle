<?php

namespace Knp\JsonSchemaBundle\Property;

use Knp\JsonSchemaBundle\Model\Property;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;

class SerializerReflectionConstraintsHandler implements PropertyHandlerInterface
{
    /**
     * @var ClassMetadataFactoryInterface
     */
    private $classMetadataFactory;

    public function __construct(ClassMetadataFactoryInterface $classMetadataFactory)
    {
        $this->classMetadataFactory = $classMetadataFactory;
    }

    public function handle($className, Property $property)
    {
        $attributeMetadata = $this->getConstraintsForProperty($className, $property);

        if (null === $attributeMetadata) {
            return;
        }

        if ($name = $attributeMetadata->getSerializedName()) {
            $property->setDisplayName($name);
        }

        if ($groups = $attributeMetadata->getGroups()) {
            $property->setGroups($groups);
        }
    }

    private function getConstraintsForProperty($className, Property $property)
    {
        $classMetadata = $this->classMetadataFactory->getMetadataFor($className);

        foreach ($classMetadata->getAttributesMetadata() as $attributeMetadata) {
            if ($attributeMetadata->name === $property->getName()) {
                return $attributeMetadata;
            }
        }

        return null;
    }
}
