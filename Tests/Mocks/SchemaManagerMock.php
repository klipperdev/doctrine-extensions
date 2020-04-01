<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensions\Tests\Mocks;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;

/**
 * Mock class for AbstractSchemaManager.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class SchemaManagerMock extends AbstractSchemaManager
{
    public function __construct(Connection $conn)
    {
        parent::__construct($conn);
    }

    /**
     * {@inheritdoc}
     */
    protected function _getPortableTableColumnDefinition($tableColumn): void
    {
    }
}
