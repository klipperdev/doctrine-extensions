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
use Doctrine\DBAL\Platforms\AbstractPlatform;

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

    private ?\Throwable $_fetchOneException = null;

    private ?Statement $_queryResult = null;

    private AbstractPlatform $_platformMock;

    private int $_lastInsertId = 0;

    private array $_inserts = [];

    private array $_executeUpdates = [];

    public function getDatabasePlatform()
    {
        return $this->_platformMock ?? $this->_platformMock = new DatabasePlatformMock();
    }

    /**
     * @param mixed $tableName
     */
    public function insert($tableName, array $data, array $types = []): void
    {
        $this->_inserts[$tableName][] = $data;
    }

    /**
     * @param mixed $query
     */
    public function executeUpdate($query, array $params = [], array $types = []): void
    {
        $this->_executeUpdates[] = ['query' => $query, 'params' => $params, 'types' => $types];
    }

    /**
     * @param null|mixed $seqName
     */
    public function lastInsertId($seqName = null)
    {
        return $this->_lastInsertId;
    }

    /**
     * @param mixed $statement
     * @param mixed $colnum
     */
    public function fetchColumn($statement, array $params = [], $colnum = 0, array $types = [])
    {
        if (null !== $this->_fetchOneException) {
            throw $this->_fetchOneException;
        }

        return $this->_fetchOneResult;
    }

    public function query(): Statement
    {
        return $this->_queryResult;
    }

    /**
     * @param mixed      $input
     * @param null|mixed $type
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

    public function setFetchOneException(\Throwable $exception = null): void
    {
        $this->_fetchOneException = $exception;
    }

    public function setDatabasePlatform(AbstractPlatform $platform): void
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
