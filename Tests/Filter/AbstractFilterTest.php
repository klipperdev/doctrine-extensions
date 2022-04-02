<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensions\Tests\Filter;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\FilterCollection;
use Klipper\Component\DoctrineExtensions\Filter\AbstractFilter;
use Klipper\Component\DoctrineExtensions\Tests\Fixtures\BarFilter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests case for abstract sql filter.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class AbstractFilterTest extends TestCase
{
    public function getParameters()
    {
        return [
            [null, ''],
            [false, ''],
            [true, 'f.foo = "bar"'],
        ];
    }

    /**
     * @dataProvider getParameters
     *
     * @param null|bool $value    The value of foo_boolean parameter
     * @param string    $expected The expected result
     */
    public function testGetRealParameter(?bool $value, string $expected): void
    {
        /** @var EntityManagerInterface|MockObject $em */
        $em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();

        /** @var ClassMetadata|MockObject $meta */
        $meta = $this->getMockBuilder(ClassMetadata::class)->disableOriginalConstructor()->getMock();

        /** @var Connection|MockObject $connection */
        $connection = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock();

        $meta->expects(static::any())
            ->method('getName')
            ->willReturn(\stdClass::class)
        ;

        $meta->expects(static::any())
            ->method('getColumnName')
            ->willReturnCallback(function ($v) {
                return $v;
            })
        ;

        $em->expects(static::any())
            ->method('getFilters')
            ->willReturn(new FilterCollection($em))
        ;

        $em->expects(static::any())
            ->method('getConnection')
            ->willReturn($connection)
        ;

        $em->expects(static::any())
            ->method('getClassMetadata')
            ->willReturnCallback(function ($v) use ($meta) {
                return $v === $meta->getName()
                    ? $meta
                    : null;
            })
        ;

        $connection->expects(static::any())
            ->method('quote')
            ->willReturnCallback(function ($v) {
                return '"'.$v.'"';
            })
        ;

        $filter = new BarFilter($em);
        static::assertInstanceOf(AbstractFilter::class, $filter);

        if (null !== $value) {
            $filter->setParameter('foo_boolean', $value, 'boolean');
        }

        static::assertSame($expected, $filter->addFilterConstraint($meta, 'f'));
    }
}
