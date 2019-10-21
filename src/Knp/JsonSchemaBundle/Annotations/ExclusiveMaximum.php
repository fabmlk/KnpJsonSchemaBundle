<?php

namespace Knp\JsonSchemaBundle\Annotations;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class ExclusiveMaximum
{
    /** @var integer */
    public $maximum;
}
