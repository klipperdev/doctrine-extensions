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
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;

/**
 * Mock class for Driver.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class DriverMock implements Driver
{
    /**
     * @var null|\Doctrine\DBAL\Platforms\AbstractPlatform
     */
    private $_platformMock;

    /**
     * @var null|\Doctrine\DBAL\Schema\AbstractSchemaManager
     */
    private $_schemaManagerMock;

    /**
     * {@inheritdoc}
     */
    public function connect(array $params, $username = null, $password = null, array $driverOptions = [])
    {
        return new DriverConnectionMock();
    }

    /**
     * {@inheritdoc}
     */
    public function getDatabasePlatform()
    {
        if (!$this->_platformMock) {
            $this->_platformMock = new DatabasePlatformMock();
        }

        return $this->_platformMock;
    }

    /**
     * {@inheritdoc}
     */
    public function getSchemaManager(Connection $conn)
    {
        if (null === $this->_schemaManagerMock) {
            return new SchemaManagerMock($conn);
        }

        return $this->_schemaManagerMock;
    }

    public function setDatabasePlatform(AbstractPlatform $platform): void
    {
        $this->_platformMock = $platform;
    }

    public function setSchemaManager(AbstractSchemaManager $sm): void
    {
        $this->_schemaManagerMock = $sm;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'mock';
    }

    /**
     * {@inheritdoc}
     */
    public function getDatabase(Connection $conn): void
    {
    }

    public function convertExceptionCode(\Exception $exception)
    {
        return 0;
    }
}
