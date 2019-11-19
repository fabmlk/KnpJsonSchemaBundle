<?php

namespace Knp\JsonSchemaBundle\Property;

use Doctrine\Common\Inflector\Inflector;
use Knp\JsonSchemaBundle\Model\Property;
use Knp\JsonSchemaBundle\Schema\SchemaRegistry;
use Symfony\Component\Form\Guess\TypeGuess;
use Symfony\Component\Form\FormTypeGuesserInterface;

class FormTypeGuesserHandler implements PropertyHandlerInterface
{
    private $guesser;

    private $registry;

    public function __construct(FormTypeGuesserInterface $guesser, SchemaRegistry $registry)
    {
        $this->guesser = $guesser;
        $this->registry = $registry;
    }

    public function handle($className, Property $property)
    {
        if ($type = $this->guesser->guessType($className, $property->getName())) {
            $property->addType($this->getPropertyType($type));
            $property->setFormat($this->getPropertyFormat($type));

// No longer works: type can never be equal to 'entity'
//            if ($type->getType() == 'entity') {
//                $options = $type->getOptions();
//
//                if (isset($options['class']) && $this->registry->hasNamespace($options['class'])) {
//                    $alias = $this->registry->getAlias($options['class']);
//
//                    if ($alias) {
//                        $property->setObject($alias);
//
//                        if (isset($options['multiple']) && $options['multiple'] == true) {
//                            $property->setMultiple(true);
//                        }
//                    }
//                }
//            }
        }

        if ($required = $this->guesser->guessRequired($className, $property->getName())) {
            $property->setRequired($required->getValue());
        }

        if ($pattern = $this->guesser->guessPattern($className, $property->getName())) {
            $property->setPattern($pattern->getValue());
        }

        if ($maximum = $this->guesser->guessMaxLength($className, $property->getName())) {
            $property->setMaximum($maximum->getValue());
        }

        if ($property->getTitle() == null) {
            $title = ucwords(str_replace('_', ' ', Inflector::tableize($property->getName())));
            $property->setTitle($title);
        }
    }

    private function getPropertyType(TypeGuess $type)
    {
        switch ($type->getType()) {
            case 'Symfony\Component\Form\Extension\Core\Type\FileType':
                return Property::TYPE_OBJECT;
            case 'Symfony\Component\Form\Extension\Core\Type\CollectionType':
                return Property::TYPE_ARRAY;
            case 'Symfony\Component\Form\Extension\Core\Type\CheckboxType':
                return Property::TYPE_BOOLEAN;
            case 'Symfony\Component\Form\Extension\Core\Type\NumberType':
                return Property::TYPE_NUMBER;
            case 'Symfony\Component\Form\Extension\Core\Type\IntegerType':
                return Property::TYPE_INTEGER;
            default:
                return Property::TYPE_STRING;
        }
    }

    private function getPropertyFormat(TypeGuess $type)
    {
        switch ($type->getType()) {
            case 'Symfony\Component\Form\Extension\Core\Type\DateType':
                return Property::FORMAT_DATE;
            case 'Symfony\Component\Form\Extension\Core\Type\DateTimeType':
                return Property::FORMAT_DATETIME;
            case 'Symfony\Component\Form\Extension\Core\Type\TimeType':
                return Property::FORMAT_TIME;
        }
    }
}
