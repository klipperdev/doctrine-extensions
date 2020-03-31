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

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Mock class for DatabasePlatform.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class DatabasePlatformMock extends AbstractPlatform
{
    /**
     * @var string
     */
    private $_sequenceNextValSql = '';

    /**
     * @var bool
     */
    private $_prefersIdentityColumns = true;

    /**
     * @var bool
     */
    private $_prefersSequences = false;

    /**
     * {@inheritdoc}
     */
    public function prefersIdentityColumns()
    {
        return $this->_prefersIdentityColumns;
    }

    /**
     * {@inheritdoc}
     */
    public function prefersSequences()
    {
        return $this->_prefersSequences;
    }

    /**
     * {@inheritdoc}
     */
    public function getSequenceNextValSQL($sequenceName)
    {
        return $this->_sequenceNextValSql;
    }

    /**
     * {@inheritdoc}
     */
    public function getBooleanTypeDeclarationSQL(array $field): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getIntegerTypeDeclarationSQL(array $field): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getBigIntTypeDeclarationSQL(array $field): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getSmallIntTypeDeclarationSQL(array $field): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getVarcharTypeDeclarationSQL(array $field): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getClobTypeDeclarationSQL(array $field): void
    {
    }

    /* MOCK API */

    /**
     * @param bool $bool
     */
    public function setPrefersIdentityColumns($bool): void
    {
        $this->_prefersIdentityColumns = $bool;
    }

    /**
     * @param bool $bool
     */
    public function setPrefersSequences($bool): void
    {
        $this->_prefersSequences = $bool;
    }

    /**
     * @param string $sql
     */
    public function setSequenceNextValSql($sql): void
    {
        $this->_sequenceNextValSql = $sql;
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
    public function getBlobTypeDeclarationSQL(array $field): void
    {
        throw DBALException::notSupported(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    protected function _getCommonIntegerTypeDeclarationSQL(array $columnDef): void
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeDoctrineTypeMappings(): void
    {
    }
}
