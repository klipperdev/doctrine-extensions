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
     * @param null|mixed $username
     * @param null|mixed $password
     */
    public function connect(array $params, $username = null, $password = null, array $driverOptions = []): DriverConnectionMock
    {
        return new DriverConnectionMock();
    }

    public function getDatabasePlatform(): DatabasePlatformMock
    {
        if (!$this->_platformMock) {
            $this->_platformMock = new DatabasePlatformMock();
        }

        return $this->_platformMock;
    }

    public function getSchemaManager(Connection $conn): AbstractSchemaManager
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

    public function getName(): string
    {
        return 'mock';
    }

    public function getDatabase(Connection $conn): void
    {
    }

    public function convertExceptionCode(\Throwable $exception)
    {
        return 0;
    }
}
