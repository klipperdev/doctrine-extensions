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
     * @var bool
     */
    private $_supportsIdentityColumns = true;

    public function prefersIdentityColumns()
    {
        return $this->_prefersIdentityColumns;
    }

    public function prefersSequences()
    {
        return $this->_prefersSequences;
    }

    public function supportsIdentityColumns()
    {
        return $this->_supportsIdentityColumns;
    }

    /**
     * @param mixed $sequenceName
     */
    public function getSequenceNextValSQL($sequenceName)
    {
        return $this->_sequenceNextValSql;
    }

    public function getBooleanTypeDeclarationSQL(array $field): void
    {
    }

    public function getIntegerTypeDeclarationSQL(array $field): void
    {
    }

    public function getBigIntTypeDeclarationSQL(array $field): void
    {
    }

    public function getSmallIntTypeDeclarationSQL(array $field): void
    {
    }

    public function getVarcharTypeDeclarationSQL(array $field): void
    {
    }

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

    public function getName()
    {
        return 'mock';
    }

    public function getBlobTypeDeclarationSQL(array $field): void
    {
        throw DBALException::notSupported(__METHOD__);
    }

    protected function _getCommonIntegerTypeDeclarationSQL(array $columnDef): void
    {
    }

    protected function initializeDoctrineTypeMappings(): void
    {
    }
}
