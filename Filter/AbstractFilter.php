<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensions\Filter;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

/**
 * Base of Doctrine Filter.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class AbstractFilter extends SQLFilter implements EnableFilterInterface
{
    /**
     * @var null|EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var bool
     */
    private $enable = true;

    /**
     * @var null|\ReflectionProperty
     */
    private $refParameters;

    /**
     * {@inheritdoc}
     */
    public function enable(): EnableFilterInterface
    {
        $this->enable = true;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function disable(): EnableFilterInterface
    {
        $this->enable = false;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled(): bool
    {
        return $this->enable;
    }

    /**
     * {@inheritdoc}
     */
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias): string
    {
        if ($this->isEnabled() && $this->supports($targetEntity)) {
            return $this->doAddFilterConstraint($targetEntity, $targetTableAlias);
        }

        return '';
    }

    /**
     * Gets the SQL query part to add to a query.
     *
     * The constraint SQL if there is available, empty string otherwise.
     *
     * @param ClassMetaData $targetEntity     The class metadata of target entity
     * @param string        $targetTableAlias The table alias of target entity
     *
     * @return string
     */
    abstract protected function doAddFilterConstraint(ClassMetadata $targetEntity, string $targetTableAlias): string;

    /**
     * Check if the target entity is supported by the sql filter.
     *
     * @param ClassMetadata $targetEntity class metadata of target entity
     *
     * @return bool
     */
    protected function supports(ClassMetadata $targetEntity): bool
    {
        return true;
    }

    /**
     * Get the entity manager.
     *
     * @throws
     *
     * @return EntityManagerInterface
     */
    protected function getEntityManager(): EntityManagerInterface
    {
        if (null === $this->entityManager) {
            $ref = new \ReflectionProperty(SQLFilter::class, 'em');
            $ref->setAccessible(true);
            $this->entityManager = $ref->getValue($this);
        }

        return $this->entityManager;
    }

    /**
     * Get the class metadata.
     *
     * @param string $classname The class name
     *
     * @return ClassMetadata
     */
    protected function getClassMetadata(string $classname): ClassMetadata
    {
        return $this->getEntityManager()->getClassMetadata($classname);
    }

    /**
     * Gets a parameter to use in a query without the output escaping.
     *
     * @param string $name The name of the parameter
     *
     * @throws \InvalidArgumentException
     * @throws \Exception
     *
     * @return null|bool|bool[]|float|float[]|int|int[]|string|string[]
     */
    protected function getRealParameter(string $name)
    {
        $this->getParameter($name);

        if (null === $this->refParameters) {
            $this->refParameters = new \ReflectionProperty(SQLFilter::class, 'parameters');
            $this->refParameters->setAccessible(true);
        }

        $parameters = $this->refParameters->getValue($this);

        return $parameters[$name]['value'];
    }
}
