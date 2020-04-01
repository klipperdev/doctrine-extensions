<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensions\Tests\Filter\Listener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Doctrine\ORM\Query\FilterCollection;
use Klipper\Component\DoctrineExtensions\Filter\Listener\AbstractFilterSubscriber;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Tests case for abstract sql filter event subscriber.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class AbstractFilterSubscriberTest extends TestCase
{
    /**
     * @throws
     */
    public function testInjectParameters(): void
    {
        /** @var EntityManagerInterface|MockObject $em */
        $em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        /** @var MockObject|RequestEvent $event */
        $event = $this->getMockBuilder(RequestEvent::class)->disableOriginalConstructor()->getMock();
        /** @var MockObject|SQLFilter $filter */
        $filter = $this->getMockBuilder(SQLFilter::class)->disableOriginalConstructor()->getMock();
        /** @var FilterCollection|MockObject $filterCollection */
        $filterCollection = $this->getMockBuilder(FilterCollection::class)->disableOriginalConstructor()->getMock();

        $em->expects(static::once())
            ->method('getFilters')
            ->willReturn($filterCollection)
        ;

        $filterCollection->expects(static::once())
            ->method('getEnabledFilters')
            ->willReturn([
                'foo' => $filter,
            ])
        ;

        /** @var AbstractFilterSubscriber|MockObject $listener */
        $listener = $this->getMockForAbstractClass(AbstractFilterSubscriber::class, [$em]);

        $listener->expects(static::once())
            ->method('supports')
            ->willReturn(SQLFilter::class)
        ;

        $listener->expects(static::once())
            ->method('injectParameters')
            ->with($filter)
        ;

        static::assertEquals([
            KernelEvents::REQUEST => [
                ['onEvent', 7],
            ],
        ], $listener::getSubscribedEvents());

        $listener->onEvent($event);
    }
}
