<?php

namespace Knp\JsonSchemaBundle\Bridge;

use Doctrine\Common\Cache\Cache;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Mapping\ClassMetadata;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\Mapping\Loader\LoaderInterface;

/**
 * Returns a {@link ClassMetadata}.
 *
 * Copied from Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory
 * with minor changes to accept traits.
 */
class TraitAwareClassMetadataFactory implements ClassMetadataFactoryInterface
{
    private $decorated;
    private $loader;
    private $cache;
    private $loadedClasses;

    public function __construct(ClassMetadataFactoryInterface $decorated, LoaderInterface $loader, Cache $cache = null)
    {
        $this->decorated = $decorated;
        $this->loader = $loader;
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadataFor($value)
    {
        try {
            return $this->decorated->getMetadataFor($value);
        } catch (\InvalidArgumentException $e) {
            $class = $this->getClass($value);
            if (!$class) {
                throw new InvalidArgumentException(sprintf('Cannot create metadata for non-objects. Got: "%s"', \gettype($value)));
            }

            if (isset($this->loadedClasses[$class])) {
                return $this->loadedClasses[$class];
            }

            if ($this->cache && ($this->loadedClasses[$class] = $this->cache->fetch($class))) {
                return $this->loadedClasses[$class];
            }

            if (!class_exists($class) && !interface_exists($class) && !trait_exists($class)) {
                throw new InvalidArgumentException(sprintf('The class or interface or trait "%s" does not exist.', $class));
            }

            $classMetadata = new ClassMetadata($class);
            $this->loader->loadClassMetadata($classMetadata);

            $reflectionClass = $classMetadata->getReflectionClass();

            // Include metadata from the parent class
            if ($parent = $reflectionClass->getParentClass()) {
                $classMetadata->merge($this->getMetadataFor($parent->name));
            }

            // Include metadata from all implemented interfaces
            foreach ($reflectionClass->getInterfaces() as $interface) {
                $classMetadata->merge($this->getMetadataFor($interface->name));
            }

            if ($this->cache) {
                $this->cache->save($class, $classMetadata);
            }

            return $this->loadedClasses[$class] = $classMetadata;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasMetadataFor($value)
    {
        if ($ret = $this->decorated->hasMetadataFor($value)) {
            return $ret;
        }

        $class = $this->getClass($value);

        return class_exists($class) || interface_exists($class) || trait_exists($class);
    }

    /**
     * Gets a class name for a given class or instance.
     *
     * @param mixed $value
     *
     * @return string|bool
     */
    private function getClass($value)
    {
        if (!\is_object($value) && !\is_string($value)) {
            return false;
        }

        return ltrim(\is_object($value) ? \get_class($value) : $value, '\\');
    }
}
