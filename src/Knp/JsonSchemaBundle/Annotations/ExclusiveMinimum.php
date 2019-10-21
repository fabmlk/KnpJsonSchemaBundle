<?php

namespace Knp\JsonSchemaBundle\Annotations;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class ExclusiveMinimum
{
    /** @var integer */
    public $minimum;
}
