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

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;

/**
 * Fixture.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @Entity
 */
class DoubleNameEntity
{
    /**
     * @Column(type="string")
     */
    public string $name;

    /**
     * @Column(type="string", nullable=true)
     */
    public ?string $name2;

    /**
     * @Id
     * @Column(type="integer")
     */
    protected int $id;

    public function __construct(int $id, string $name, ?string $name2)
    {
        $this->id = $id;
        $this->name = $name;
        $this->name2 = $name2;
    }
}
