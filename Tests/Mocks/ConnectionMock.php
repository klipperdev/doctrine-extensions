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
use Doctrine\DBAL\Driver\Statement;

/**
 * Mock class for Connection.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class ConnectionMock extends Connection
{
    /**
     * @var mixed
     */
    private $_fetchOneResult;

    /**
     * @var null|\Exception
     */
    private $_fetchOneException;

    /**
     * @var null|Statement
     */
    private $_queryResult;

    /**
     * @var DatabasePlatformMock
     */
    private $_platformMock;

    /**
     * @var int
     */
    private $_lastInsertId = 0;

    /**
     * @var array
     */
    private $_inserts = [];

    /**
     * @var array
     */
    private $_executeUpdates = [];

    /**
     * @param \Doctrine\DBAL\Driver              $driver
     * @param null|\Doctrine\DBAL\Configuration  $config
     * @param null|\Doctrine\Common\EventManager $eventManager
     */
    public function __construct(array $params, $driver, $config = null, $eventManager = null)
    {
        $this->_platformMock = new DatabasePlatformMock();

        parent::__construct($params, $driver, $config, $eventManager);

        // Override possible assignment of platform to database platform mock
        $this->_platform = $this->_platformMock;
    }

    /**
     * {@inheritdoc}
     */
    public function getDatabasePlatform()
    {
        return $this->_platformMock;
    }

    /**
     * {@inheritdoc}
     */
    public function insert($tableName, array $data, array $types = []): void
    {
        $this->_inserts[$tableName][] = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function executeUpdate($query, array $params = [], array $types = []): void
    {
        $this->_executeUpdates[] = ['query' => $query, 'params' => $params, 'types' => $types];
    }

    /**
     * {@inheritdoc}
     */
    public function lastInsertId($seqName = null)
    {
        return $this->_lastInsertId;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchColumn($statement, array $params = [], $colnum = 0, array $types = [])
    {
        if (null !== $this->_fetchOneException) {
            throw $this->_fetchOneException;
        }

        return $this->_fetchOneResult;
    }

    /**
     * {@inheritdoc}
     */
    public function query(): Statement
    {
        return $this->_queryResult;
    }

    /**
     * {@inheritdoc}
     */
    public function quote($input, $type = null)
    {
        if (\is_string($input)) {
            return "'".$input."'";
        }

        return $input;
    }

    /* Mock API */

    /**
     * @param mixed $fetchOneResult
     */
    public function setFetchOneResult($fetchOneResult): void
    {
        $this->_fetchOneResult = $fetchOneResult;
    }

    public function setFetchOneException(\Exception $exception = null): void
    {
        $this->_fetchOneException = $exception;
    }

    /**
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
     */
    public function setDatabasePlatform($platform): void
    {
        $this->_platformMock = $platform;
    }

    /**
     * @param int $id
     */
    public function setLastInsertId($id): void
    {
        $this->_lastInsertId = $id;
    }

    public function setQueryResult(Statement $result): void
    {
        $this->_queryResult = $result;
    }

    /**
     * @return array
     */
    public function getInserts()
    {
        return $this->_inserts;
    }

    /**
     * @return array
     */
    public function getExecuteUpdates()
    {
        return $this->_executeUpdates;
    }

    public function reset(): void
    {
        $this->_inserts = [];
        $this->_lastInsertId = 0;
    }
}
