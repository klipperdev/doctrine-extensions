<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensions\Tests\Models;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity
 * @Table(name="users")
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class UserMock
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    public $id;

    /**
     * @Column(type="string", length=255, unique=true)
     */
    public $username;

    public function getId()
    {
        return $this->id;
    }

    public function getUsername()
    {
        return $this->username;
    }
}
