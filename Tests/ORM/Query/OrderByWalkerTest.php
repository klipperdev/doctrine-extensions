<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensions\Tests\ORM\Query;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Klipper\Component\DoctrineExtensions\ORM\Query\OrderByWalker;
use Klipper\Component\DoctrineExtensions\Tests\AbstractOrmTestCase;

/**
 * Tests case for order by walker.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class OrderByWalkerTest extends AbstractOrmTestCase
{
    private ?EntityManagerInterface $em = null;

    protected function setUp(): void
    {
        $this->em = $this->_getTestEntityManager();
    }

    public function testOrderSingleField(): void
    {
        $dqlToBeTested = 'SELECT u FROM Klipper\Component\DoctrineExtensions\Tests\Models\UserMock u';
        $treeWalkers = [OrderByWalker::class];

        $query = $this->em->createQuery($dqlToBeTested);
        $query->setHint(Query::HINT_CUSTOM_TREE_WALKERS, $treeWalkers)
            ->useQueryCache(false)
        ;

        OrderByWalker::addHint($query, ['u'], ['username'], ['desc']);

        $expected = 'SELECT u0_.id AS id_0, u0_.username AS username_1 FROM users u0_ ORDER BY u0_.username DESC';
        static::assertSame($expected, $query->getSQL());
    }

    public function testOrderMultipleFields(): void
    {
        $dqlToBeTested = 'SELECT u FROM Klipper\Component\DoctrineExtensions\Tests\Models\UserMock u';
        $treeWalkers = [OrderByWalker::class];

        $query = $this->em->createQuery($dqlToBeTested);
        $query->setHint(Query::HINT_CUSTOM_TREE_WALKERS, $treeWalkers)
            ->useQueryCache(false)
        ;

        OrderByWalker::addHint($query, ['u', 'u'], ['username', 'id'], ['desc', 'asc']);

        $expected = 'SELECT u0_.id AS id_0, u0_.username AS username_1 FROM users u0_ ORDER BY u0_.username DESC, u0_.id ASC';
        static::assertSame($expected, $query->getSQL());
    }

    public function testOrderWithoutField(): void
    {
        $dqlToBeTested = 'SELECT u FROM Klipper\Component\DoctrineExtensions\Tests\Models\UserMock u';
        $treeWalkers = [OrderByWalker::class];

        $query = $this->em->createQuery($dqlToBeTested);
        $query->setHint(Query::HINT_CUSTOM_TREE_WALKERS, $treeWalkers)
            ->useQueryCache(false)
        ;

        $expected = 'SELECT u0_.id AS id_0, u0_.username AS username_1 FROM users u0_';
        static::assertSame($expected, $query->getSQL());
    }

    public function testOrderWithInvalidAliases(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The HINT_SORT_ALIAS and HINT_SORT_DIRECTION must be an array');

        $dqlToBeTested = 'SELECT u FROM Klipper\Component\DoctrineExtensions\Tests\Models\UserMock u';
        $treeWalkers = [OrderByWalker::class];

        $query = $this->em->createQuery($dqlToBeTested);
        $query->setHint(Query::HINT_CUSTOM_TREE_WALKERS, $treeWalkers)
            ->useQueryCache(false)
        ;

        $query->setHint(OrderByWalker::HINT_SORT_ALIAS, 'u');
        $query->setHint(OrderByWalker::HINT_SORT_FIELD, ['username']);
        $query->setHint(OrderByWalker::HINT_SORT_DIRECTION, 'desc');

        $query->getSQL();
    }

    public function testOrderWithInvalidAliasComponent(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('There is no component aliased by [a] in the given Query');

        $dqlToBeTested = 'SELECT u FROM Klipper\Component\DoctrineExtensions\Tests\Models\UserMock u';
        $treeWalkers = [OrderByWalker::class];

        $query = $this->em->createQuery($dqlToBeTested);
        $query->setHint(Query::HINT_CUSTOM_TREE_WALKERS, $treeWalkers)
            ->useQueryCache(false)
        ;

        OrderByWalker::addHint($query, ['a'], ['username'], ['desc']);

        $query->getSQL();
    }

    public function testOrderWithInvalidField(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('There is no such field [foo] in the given Query component, aliased by [u]');

        $dqlToBeTested = 'SELECT u FROM Klipper\Component\DoctrineExtensions\Tests\Models\UserMock u';
        $treeWalkers = [OrderByWalker::class];

        $query = $this->em->createQuery($dqlToBeTested);
        $query->setHint(Query::HINT_CUSTOM_TREE_WALKERS, $treeWalkers)
            ->useQueryCache(false)
        ;

        OrderByWalker::addHint($query, ['u'], ['foo'], ['desc']);

        $query->getSQL();
    }

    public function testOrderWithoutAliasAndComponent(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('There is no component field [username] in the given Query');

        $dqlToBeTested = 'SELECT u FROM Klipper\Component\DoctrineExtensions\Tests\Models\UserMock u';
        $treeWalkers = [OrderByWalker::class];

        $query = $this->em->createQuery($dqlToBeTested);
        $query->setHint(Query::HINT_CUSTOM_TREE_WALKERS, $treeWalkers)
            ->useQueryCache(false)
        ;

        $query->setHint(OrderByWalker::HINT_SORT_ALIAS, [false]);
        $query->setHint(OrderByWalker::HINT_SORT_FIELD, ['username']);
        $query->setHint(OrderByWalker::HINT_SORT_DIRECTION, ['desc']);

        $query->getSQL();
    }
}
