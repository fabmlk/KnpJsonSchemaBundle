<?php

namespace Knp\JsonSchemaBundle\Property;

use Knp\JsonSchemaBundle\Model\Property;
use Symfony\Component\Validator\Mapping\Factory\MetadataFactoryInterface;

class ExtraValidatorConstraintsHandler implements PropertyHandlerInterface
{
    private $classMetadataFactory;

    public function __construct(MetadataFactoryInterface $classMetadataFactory)
    {
        $this->classMetadataFactory = $classMetadataFactory;
    }

    public function handle($className, Property $property)
    {
        $this->doHandle($this->getConstraintsForProperty($className, $property), $property);
    }

    private function doHandle(iterable $constraints, Property $property)
    {
        foreach ($constraints as $constraint) {
            if ($constraint instanceof \Symfony\Component\Validator\Constraints\Choice) {
                if ($constraint->callback && is_callable($constraint->callback)) {
                    $choices = call_user_func($constraint->callback);
                } else {
                    $choices = $constraint->choices;
                }
                $property->setEnumeration($choices);
            }
            if ($constraint instanceof \Symfony\Component\Validator\Constraints\Length) {
                $property->setMinimum($constraint->min);
                $property->setMaximum($constraint->max);
            }
            if ($constraint instanceof \Symfony\Component\Validator\Constraints\Type &&
                false === $property->getMultiple()) {
                $property->addType($this->getPropertyType($constraint));
                if ('DateTime' === $constraint->type) {
                    $property->setFormat(Property::FORMAT_DATETIME);
                }
            }
            if ($constraint instanceof \Symfony\Component\Validator\Constraints\Date) {
                $property->setFormat(Property::FORMAT_DATE);
            }
            if ($constraint instanceof \Symfony\Component\Validator\Constraints\DateTime) {
                $property->setFormat(Property::FORMAT_DATETIME);
            }
            if ($constraint instanceof \Symfony\Component\Validator\Constraints\Time) {
                $property->setFormat(Property::FORMAT_TIME);
            }
            if ($constraint instanceof \Symfony\Component\Validator\Constraints\Email) {
                $property->setFormat(Property::FORMAT_EMAIL);
            }
            if ($constraint instanceof \Symfony\Component\Validator\Constraints\Ip) {
                if ('4' === $constraint->version) {
                    $property->setFormat(Property::FORMAT_IPADDRESS);
                } elseif ('6' === $constraint->version) {
                    $property->setFormat(Property::FORMAT_IPV6);
                }
            }
            if ($constraint instanceof \Symfony\Component\Validator\Constraints\Unique) {
                $property->setUnique(true);
            }
            if ($constraint instanceof \Symfony\Component\Validator\Constraints\All) {
                // only the type from the array elements will be saved as the property
                // itself will have the type "array" anyway since multiple is true
                $property->setType(null);
                $this->doHandle($constraint->constraints, $property);
                $property->setMultiple(true);
            }
        }
    }

    private function getConstraintsForProperty($className, Property $property)
    {
        $classMetadata = $this->classMetadataFactory->getMetadataFor($className);

        foreach ($classMetadata->properties as $propertyMetadata) {
            if ($propertyMetadata->name === $property->getName()) {
                return $propertyMetadata->constraints;
            }
        }

        return array();
    }

    // https://symfony.com/doc/current/reference/constraints/Type.html
    private function getPropertyType(\Symfony\Component\Validator\Constraints\Type $type)
    {
        switch ($type->type) {
            case 'array':
            case 'iterable':
                return Property::TYPE_ARRAY;
            case 'bool':
                return Property::TYPE_BOOLEAN;
            case 'numeric':
            case 'float':
            case 'real':
            case 'long':
            case 'double':
                return Property::TYPE_NUMBER;
            case 'integer':
            case 'int':
                return Property::TYPE_INTEGER;
            case 'null':
                return Property::TYPE_NULL;
            case 'resource':
                return Property::TYPE_OBJECT;
            default:
                return Property::TYPE_STRING;
        }
    }
}
