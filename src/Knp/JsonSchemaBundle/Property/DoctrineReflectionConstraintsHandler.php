<?php

namespace Knp\JsonSchemaBundle\Property;

use Knp\JsonSchemaBundle\Model\Property;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\DBAL\Types\Types;

class DoctrineReflectionConstraintsHandler implements PropertyHandlerInterface
{
    private $em;

    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
    }

    public function handle($className, Property $property)
    {
        $type = $this->getConstraintsForProperty($className, $property);
        
        if (in_array($type, array(Types::BIGINT, Types::SMALLINT, Types::INTEGER))) {
            $property->addType(Property::TYPE_INTEGER);
        }
        if (in_array($type, array(Types::BIGINT, Types::SMALLINT, Types::INTEGER, Types::DECIMAL, Types::FLOAT))) {
            $property->addType(Property::TYPE_NUMBER);
        }
        if (in_array($type, array(Types::STRING, Types::TEXT))) {
            $property->addType(Property::TYPE_STRING);
        }
        if ($type == Types::BOOLEAN) {
            $property->addType(Property::TYPE_BOOLEAN);
        }
        if ($type == Types::ARRAY) {
            $property->addType(Property::TYPE_ARRAY);
        }
        if ($type == Types::OBJECT) {
            $property->setObject($this->em->getMetadataFactory()->getMetadataFor($className)->getAssociationTargetClass($property->getName()));
            $property->addType(Property::TYPE_OBJECT);
        }
        if (in_array($type, array(Types::DATE_MUTABLE, Types::DATE_IMMUTABLE))) {
            $property->setFormat(Property::FORMAT_DATE);
        }
        if (in_array($type, array(Types::DATETIME_MUTABLE, Types::DATETIMETZ_MUTABLE, Types::DATETIME_IMMUTABLE, Types::DATETIMETZ_IMMUTABLE))) {
            $property->setFormat(Property::FORMAT_DATETIME);
        }
        if (in_array($type, array(Types::TIME_MUTABLE, Types::TIME_IMMUTABLE))) {
            $property->setFormat(Property::FORMAT_TIME);
        }
    }

    private function getConstraintsForProperty($className, Property $property)
    {
        $metadataFactory = $this->em->getMetadataFactory();
        // not an entity ?
        if ($metadataFactory->isTransient($className)) {
            return array();
        }

        $classMetadata = $metadataFactory->getMetadataFor($className);
        foreach ($classMetadata->getFieldNames() as $fieldName) {
            if ($fieldName === $property->getName()) {
                return $classMetadata->getTypeOfField($fieldName);
            }
        }

        foreach ($classMetadata->getAssociationNames() as $associationName) {
            if ($associationName === $property->getName()) {
                return ($classMetadata->isSingleValuedAssociation($associationName) ? Types::OBJECT :
                       ($classMetadata->isCollectionValuedAssociation($associationName) ? Types::ARRAY : Property::TYPE_NULL));
            }
        }

        return Property::TYPE_NULL;
    }
}
