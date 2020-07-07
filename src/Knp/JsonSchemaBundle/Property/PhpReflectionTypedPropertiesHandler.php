<?php

namespace Knp\JsonSchemaBundle\Property;

use Knp\JsonSchemaBundle\Model\Property;

/**
 * Inspired by https://github.com/symfony/property-info/blob/5.1/Extractor/ReflectionExtractor.php
 */
class PhpReflectionTypedPropertiesHandler implements PropertyHandlerInterface
{
    public function handle($className, Property $property)
    {
        if (\PHP_VERSION_ID >= 70400) {
            try {
                $reflectionProperty = new \ReflectionProperty($className, $property->getName());
                $type = $reflectionProperty->getType();
                if (null !== $type) {
                    $phpTypeOrClass = $type instanceof \ReflectionNamedType ? $type->getName() : (string) $type;

                    $property->setType($this->getPropertyType($phpTypeOrClass));
                    $property->setFormat($this->getPropertyFormat($phpTypeOrClass));
                }
            } catch (\ReflectionException $e) {
                // noop
            }
        }
    }

    private function getPropertyType($phpTypeOrClass)
    {
        // https://wiki.php.net/rfc/typed_properties_v2#supported_types
        switch ($phpTypeOrClass) {
            case 'array':
            case 'iterable':
                return Property::TYPE_ARRAY;
            case 'float':
                return Property::TYPE_NUMBER;
            case 'bool':
                return Property::TYPE_BOOLEAN;
            case 'int':
                return Property::TYPE_INTEGER;
            case 'string':
            case 'DateTime':
            case 'DateTimeInterface':
            case 'DateTimeImmutable':
                return Property::TYPE_STRING;
            default:
                return Property::TYPE_OBJECT;
        }
    }

    private function getPropertyFormat($phpTypeOrClass)
    {
        switch ($phpTypeOrClass) {
            case 'DateTime':
            case 'DateTimeInterface':
            case 'DateTimeImmutable':
                return Property::FORMAT_DATETIME;
        }
    }
}