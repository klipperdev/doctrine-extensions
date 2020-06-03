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
class CompositeIntIdEntity
{
    /** @Column(type="string") */
    public string $name;
    /**
     * @Id
     * @Column(type="integer")
     */
    protected int $id1;

    /**
     * @Id
     * @Column(type="integer")
     */
    protected int $id2;

    public function __construct(int $id1, int $id2, string $name)
    {
        $this->id1 = $id1;
        $this->id2 = $id2;
        $this->name = $name;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
