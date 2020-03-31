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

use Doctrine\DBAL\Driver\Statement;

/**
 * This class is a mock of the Statement interface.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class StatementMock implements \IteratorAggregate, Statement
{
    /**
     * {@inheritdoc}
     */
    public function bindValue($param, $value, $type = null): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function bindParam($column, &$variable, $type = null, $length = null): void
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

    /**
     * {@inheritdoc}
     */
    public function execute($params = null): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function rowCount(): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function closeCursor(): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function columnCount(): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setFetchMode($fetchStyle, $arg2 = null, $arg3 = null): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($fetchMode = null, $cursorOrientation = \PDO::FETCH_ORI_NEXT, $cursorOffset = 0): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAll($fetchMode = null, $fetchArgument = null, $ctorArgs = null): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function fetchColumn($columnIndex = 0): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): void
    {
    }
}
