<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensions\Tests\Util;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Doctrine\ORM\Query\FilterCollection;
use Klipper\Component\DoctrineExtensions\Tests\Fixtures\BarFilter;
use Klipper\Component\DoctrineExtensions\Util\SqlFilterUtil;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests case for abstract sql filter.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class SqlFilterUtilTest extends TestCase
{
    /**
     * @var EntityManagerInterface|MockObject
     */
    protected $em;

    /**
     * @var FilterCollection|MockObject
     */
    protected $filterCollection;

    protected function setUp(): void
    {
        $this->em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->filterCollection = $this->getMockBuilder(FilterCollection::class)->disableOriginalConstructor()->getMock();

        $this->em->expects($this->once())
            ->method('getFilters')
            ->willReturn($this->filterCollection)
        ;
    }

    public function testGetEnabledFilters(): void
    {
        /** @var MockObject|SQLFilter $filter */
        $filter = $this->getMockForAbstractClass(SQLFilter::class, [$this->em]);
        $barFilter = new BarFilter($this->em);
        $barFilter->disable();
        $expected = [
            'foo' => $filter,
        ];

        $this->filterCollection->expects($this->once())
            ->method('getEnabledFilters')
            ->willReturn(array_merge($expected, [
                'bar' => $barFilter,
            ]))
        ;

        $this->assertEquals($expected, SqlFilterUtil::getEnabledFilters($this->em));
    }

    public function testIsEnabledWithDisabledSqlFilter(): void
    {
        $this->filterCollection->expects($this->once())
            ->method('isEnabled')
            ->with('foo')
            ->willReturn(false)
        ;

        $this->assertFalse(SqlFilterUtil::isEnabled($this->em, 'foo'));
    }

    public function testIsEnabledWithDisabledEnableSqlFilter(): void
    {
        $barFilter = new BarFilter($this->em);
        $barFilter->disable();

        $this->filterCollection->expects($this->once())
            ->method('isEnabled')
            ->with('bar')
            ->willReturn(true)
        ;

        $this->filterCollection->expects($this->once())
            ->method('getFilter')
            ->willReturn($barFilter)
        ;

        $this->assertFalse(SqlFilterUtil::isEnabled($this->em, 'bar'));
    }

    public function testIsEnabledWithEnabledEnableSqlFilter(): void
    {
        $barFilter = new BarFilter($this->em);

        $this->filterCollection->expects($this->once())
            ->method('isEnabled')
            ->with('bar')
            ->willReturn(true)
        ;

        $this->filterCollection->expects($this->once())
            ->method('getFilter')
            ->willReturn($barFilter)
        ;

        $this->assertTrue(SqlFilterUtil::isEnabled($this->em, 'bar'));
    }
}
