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

use Doctrine\ORM\Mapping\ClassMetadata;
use Klipper\Component\DoctrineExtensions\Filter\AbstractFilter;

/**
 * Fixture filter.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class BarFilter extends AbstractFilter
{
    protected function doAddFilterConstraint(ClassMetadata $targetEntity, string $targetTableAlias): string
    {
        $filter = '';

        try {
            if ($this->hasParameter('foo_boolean') && $this->getRealParameter('foo_boolean')) {
                $connection = $this->getEntityManager()->getConnection();
                $col = $this->getClassMetadata($targetEntity->getName())->getColumnName('foo');
                $filter .= $targetTableAlias.'.'.$col.' = '.$connection->quote('bar');
            }
        } catch (\Throwable $e) {
            // nothing do
        }

        return $filter;
    }
}
