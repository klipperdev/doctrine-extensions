<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensions\Validator\Constraints;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity as BaseUniqueEntity;

/**
 * Constraint for the Unique Entity validator with disable sql filter option.
 *
 * @Annotation
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class UniqueEntity extends BaseUniqueEntity
{
    public $service = 'klipper.doctrine_extensions.orm.validator.unique';
    public $filters = [];
    public $allFilters = true;
}
