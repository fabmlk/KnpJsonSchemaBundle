<?php

namespace Knp\JsonSchemaBundle\Model;

use Knp\JsonSchemaBundle\Model\Schema;
use Symfony\Component\Validator\Constraint;

class Property implements \JsonSerializable
{
    const TYPE_STRING = 'string';
    const TYPE_NUMBER = 'number';
    const TYPE_INTEGER = 'integer';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_OBJECT = 'object';
    const TYPE_ARRAY = 'array';
    const TYPE_NULL = 'null';
    const TYPE_ANY = 'any';
    const FORMAT_DATETIME = 'date-time';
    const FORMAT_DATE = 'date';
    const FORMAT_TIME = 'time';
    const FORMAT_UTCMILLISEC = 'utc-millisec';
    const FORMAT_REGEX = 'regex';
    const FORMAT_COLOR = 'color';
    const FORMAT_STYLE = 'style';
    const FORMAT_PHONE = 'phone';
    const FORMAT_URI = 'uri';
    const FORMAT_EMAIL = 'email';
    const FORMAT_IPADDRESS = 'ip-address';
    const FORMAT_IPV6 = 'ipv6';
    const FORMAT_HOSTNAME = 'host-name';

    protected $name;
    protected $displayName;
    protected $title;
    protected $description;
    protected $required = false;
    protected $type = array();
    protected $pattern;
    protected $enumeration = array();
    protected $minimum;
    protected $maximum;
    protected $exclusiveMinimum = false;
    protected $exclusiveMaximum = false;
    protected $format;
    protected $options;
    protected $enum;
    protected $disallowed = array();
    protected $ignored = false;
    protected $object;
    protected $multiple = false;
    protected $schema;
    protected $default;
    protected $unique = false;
    protected $groups = array();

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setDisplayName($name)
    {
        $this->displayName = $name;
    }

    public function getDisplayName()
    {
        return $this->displayName;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setRequired($required)
    {
        $this->required = $required;

        return $this;
    }

    public function isRequired()
    {
        return $this->required;
    }

    public function hasType($type)
    {
        return (!is_null($type) && in_array($type, $this->type));
    }

    public function addType($type)
    {
        if (!in_array($type, $this->type) && !is_null($type)) {
            $this->type[] = $type;
        }

        return $this;
    }

    public function setType($type)
    {
        $this->type = (array)$type;

        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setPattern($pattern)
    {
        $this->pattern = $pattern;

        return $this;
    }

    public function getPattern()
    {
        return $this->pattern;
    }

    public function setEnumeration(array $enumeration)
    {
        $this->enumeration = $enumeration;

        return $this;
    }

    public function getEnumeration()
    {
        return $this->enumeration;
    }

    public function setMinimum($minimum)
    {
        $this->minimum = $minimum;

        return $this;
    }

    public function getMinimum()
    {
        return $this->minimum;
    }

    public function setMaximum($maximum)
    {
        $this->maximum = $maximum;

        return $this;
    }

    public function getMaximum()
    {
        return $this->maximum;
    }

    public function setExclusiveMinimum($exclusiveMinimum)
    {
        $this->exclusiveMinimum = $exclusiveMinimum;

        return $this;
    }

    public function getExclusiveMinimum()
    {
        return $this->exclusiveMinimum;
    }

    public function setExclusiveMaximum($exclusiveMaximum)
    {
        $this->exclusiveMaximum = $exclusiveMaximum;

        return $this;
    }

    public function getExclusiveMaximum()
    {
        return $this->exclusiveMaximum;
    }

    public function setFormat($format)
    {
        if (!\is_null($format)) {
            $this->format = $format;
        }

        return $this;
    }

    public function getFormat()
    {
        return $this->format;
    }

    public function setOptions(array $options)
    {
        $this->options = $options;

        return $this;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setEnum(array $enum)
    {
        $this->enum = $enum;

        return $this;
    }

    public function getEnum()
    {
        return $this->enum;
    }

    public function setDisallowed(array $disallowed)
    {
        $this->disallowed = $disallowed;

        return $this;
    }

    public function getDisallowed()
    {
        return $this->disallowed;
    }

    public function isIgnored()
    {
        return $this->ignored;
    }

    public function setIgnored($ignored)
    {
        $this->ignored = $ignored;

        return $this;
    }

    public function setObject($object)
    {
        $this->object = $object;

        return $this;
    }

    public function getObject()
    {
        return $this->object;
    }

    public function setMultiple($multiple)
    {
        $this->multiple = $multiple;

        return $this;
    }

    public function getMultiple()
    {
        return $this->multiple;
    }

    public function setSchema(Schema $schema)
    {
        $this->schema = $schema;

        return $this;
    }

    public function setDefault($value)
    {
        $this->default = $value;

        return $this;
    }

    public function getDefault($value)
    {
        return $this->default;
    }

    public function setUnique($unique)
    {
        $this->unique = $unique;
    }

    public function isUnique($unique)
    {
        return $this->unique;
    }

    public function getGroups()
    {
        return $this->groups;
    }

    public function setGroups(array $groups)
    {
        $this->groups = $groups;
    }

    /**
     * @return Schema
     */
    public function getSchema()
    {
        return $this->schema;
    }

    public function jsonSerialize()
    {
        $serialized = array();
        if (!empty($this->type)) {
            if (count($this->type) === 1) {
                $serialized['type'] = $this->type[0];
            } else {
                $serialized['type'] = $this->type;
            }
        }

        if ($this->pattern) {
            $serialized['pattern'] = $this->pattern;
        }

        if (count($this->enumeration)) {
            $serialized['enum'] = $this->enumeration;
        }

        if (count(array_intersect($this->type, array(self::TYPE_NUMBER, self::TYPE_INTEGER))) >= 1) {
            if ($this->minimum) {
                $serialized['minimum']          = $this->minimum;
                $serialized['exclusiveMinimum'] = $this->exclusiveMinimum;
            }
            if ($this->maximum) {
                $serialized['maximum']          = $this->maximum;
                $serialized['exclusiveMaximum'] = $this->exclusiveMaximum;
            }
        }

        if (count(array_intersect($this->type, array(self::TYPE_STRING))) >= 1) {
            if ($this->minimum) {
                $serialized['minLength'] = $this->minimum;
            }
            if ($this->maximum) {
                $serialized['maxLength'] = $this->maximum;
            }
        }

        if ($this->format) {
            $serialized['format'] = $this->format;
        }

        if ($this->options) {
            $serialized['options'] = $this->options;
        }

        if ($this->enum) {
            $serialized['enum'] = $this->enum;
        }

        if ($this->disallowed) {
            $serialized['disallow'] = $this->disallowed;
        }

        if ($this->title) {
            $serialized['title'] = $this->title;
        }

        if ($this->description) {
            $serialized['description'] = $this->description;
        }

        if ($this->default) {
            $serialized['default'] = $this->default;
        }

        if ($this->unique) {
            $serialized['uniqueItems'] = true;
        }

        if ($this->schema && $this->hasType(self::TYPE_OBJECT)) {
            $schema = $this->schema->jsonSerialize();
            unset($schema['$schema'], $schema['id']);

            if ($this->multiple) {
                $serialized['type'] = 'array';
                $serialized['items'] = $schema;
            } else {
                $serialized = $serialized + $schema;
            }
        } else if ($this->multiple) {
            $parentserialized = array();
            $parentserialized['type'] = 'array';
            $parentserialized['items'] = $serialized;
            $serialized = $parentserialized;
        }

        return $serialized;
    }
}
