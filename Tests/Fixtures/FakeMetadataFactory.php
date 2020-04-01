<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensions\Tests\Fixtures;

use Symfony\Component\Validator\Exception\NoSuchMetadataException;
use Symfony\Component\Validator\Mapping\Factory\MetadataFactoryInterface;
use Symfony\Component\Validator\Mapping\MetadataInterface;

/**
 * Fixture.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class FakeMetadataFactory implements MetadataFactoryInterface
{
    protected $metadatas = [];

    public function getMetadataFor($class): MetadataInterface
    {
        $hash = null;

        if (\is_object($class)) {
            $hash = spl_object_hash($class);
            $class = \get_class($class);
        }

        if (!\is_string($class)) {
            throw new NoSuchMetadataException(sprintf('No metadata for type "%s".', \is_object($class) ? \get_class($class) : \gettype($class)));
        }

        if (!isset($this->metadatas[$class])) {
            if (isset($this->metadatas[$hash])) {
                return $this->metadatas[$hash];
            }

            throw new NoSuchMetadataException(sprintf('No metadata for "%s"', $class));
        }

        return $this->metadatas[$class];
    }

    public function hasMetadataFor($class): bool
    {
        $hash = null;

        if (\is_object($class)) {
            $hash = spl_object_hash($class);
            $class = \get_class($class);
        }

        if (!\is_string($class)) {
            return false;
        }

        return isset($this->metadatas[$class]) || isset($this->metadatas[$hash]);
    }

    public function addMetadata($metadata): void
    {
        $this->metadatas[$metadata->getClassName()] = $metadata;
    }

    public function addMetadataForValue($value, MetadataInterface $metadata): void
    {
        $key = \is_object($value) ? spl_object_hash($value) : $value;
        $this->metadatas[$key] = $metadata;
    }
}
