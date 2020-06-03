<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensions\Tests\Validator\Constraints;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Klipper\Component\DoctrineExtensions\Validator\Constraints\Util;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests case for util.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class UtilTest extends TestCase
{
    public function getIdentifierTypes(): array
    {
        return [
            ['bigint', 0],
            ['decimal', 0],
            ['integer', 0],
            ['smallint', 0],
            ['float', 0],
            ['guid', '00000000-0000-0000-0000-000000000000'],
            ['other', ''],
        ];
    }

    /**
     * @dataProvider getIdentifierTypes
     *
     * @param int|string $expected
     */
    public function testFormatEmptyIdentifier(string $identifierType, $expected): void
    {
        /** @var ClassMetadata|MockObject $meta */
        $meta = $this->getMockBuilder(ClassMetadata::class)->getMock();
        $meta->expects(static::any())
            ->method('getIdentifier')
            ->willReturn(['id'])
        ;

        $meta->expects(static::any())
            ->method('getTypeOfField')
            ->with('id')
            ->willReturn($identifierType)
        ;

        static::assertSame($expected, Util::formatEmptyIdentifier($meta));
    }

    /**
     * @dataProvider getIdentifierTypes
     *
     * @param int|string $expected
     */
    public function testGetFormattedIdentifier(string $identifierType, $expected): void
    {
        $fieldName = 'single';
        $value = null;
        $criteria = [
            $fieldName => new \stdClass(),
        ];

        /** @var ClassMetadata|MockObject $meta */
        $meta = $this->getMockBuilder(ClassMetadata::class)->getMock();
        $meta->expects(static::any())
            ->method('getIdentifier')
            ->willReturn([$fieldName])
        ;

        $meta->expects(static::any())
            ->method('getTypeOfField')
            ->with($fieldName)
            ->willReturn($identifierType)
        ;

        static::assertSame($expected, Util::getFormattedIdentifier($meta, $criteria, $fieldName, $value));
    }
}
