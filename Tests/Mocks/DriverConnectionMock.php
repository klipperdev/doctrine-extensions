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

use Doctrine\DBAL\Driver\Connection;

/**
 * Mock class for DriverConnection.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class DriverConnectionMock implements Connection
{
    /**
     * @var \Doctrine\DBAL\Driver\Statement
     */
    private $statementMock;

    /**
     * @return \Doctrine\DBAL\Driver\Statement
     */
    public function getStatementMock()
    {
        return $this->statementMock;
    }

    /**
     * @param \Doctrine\DBAL\Driver\Statement $statementMock
     */
    public function setStatementMock($statementMock): void
    {
        $this->statementMock = $statementMock;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare($prepareString)
    {
        return $this->statementMock ?: new StatementMock();
    }

    /**
     * {@inheritdoc}
     */
    public function query()
    {
        return $this->statementMock ?: new StatementMock();
    }

    /**
     * {@inheritdoc}
     */
    public function quote($input, $type = \PDO::PARAM_STR): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function exec($statement): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function lastInsertId($name = null): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function beginTransaction(): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function commit(): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function rollBack(): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function errorCode(): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function errorInfo(): void
    {
    }
}
