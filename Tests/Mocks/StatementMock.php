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
     * @param mixed      $param
     * @param mixed      $value
     * @param null|mixed $type
     */
    public function bindValue($param, $value, $type = null): void
    {
    }

    /**
     * @param mixed      $column
     * @param mixed      $variable
     * @param null|mixed $type
     * @param null|mixed $length
     */
    public function bindParam($column, &$variable, $type = null, $length = null): void
    {
    }

    public function errorCode(): void
    {
    }

    public function errorInfo(): void
    {
    }

    /**
     * @param null|mixed $params
     */
    public function execute($params = null): void
    {
    }

    public function rowCount(): void
    {
    }

    public function closeCursor(): void
    {
    }

    public function columnCount(): void
    {
    }

    /**
     * @param mixed      $fetchStyle
     * @param null|mixed $arg2
     * @param null|mixed $arg3
     */
    public function setFetchMode($fetchStyle, $arg2 = null, $arg3 = null): void
    {
    }

    /**
     * @param null|mixed $fetchMode
     * @param mixed      $cursorOrientation
     * @param mixed      $cursorOffset
     */
    public function fetch($fetchMode = null, $cursorOrientation = \PDO::FETCH_ORI_NEXT, $cursorOffset = 0): void
    {
    }

    /**
     * @param null|mixed $fetchMode
     * @param null|mixed $fetchArgument
     * @param null|mixed $ctorArgs
     */
    public function fetchAll($fetchMode = null, $fetchArgument = null, $ctorArgs = null): void
    {
    }

    /**
     * @param mixed $columnIndex
     */
    public function fetchColumn($columnIndex = 0): void
    {
    }

    public function getIterator(): void
    {
    }
}
