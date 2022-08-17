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

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Mock class for DatabasePlatform.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class DatabasePlatformMock extends AbstractPlatform
{
    private string $_sequenceNextValSql = '';

    private bool $_prefersIdentityColumns = true;

    private bool $_prefersSequences = false;

    private bool $_supportsIdentityColumns = true;

    public function prefersIdentityColumns(): bool
    {
        return $this->_prefersIdentityColumns;
    }

    public function prefersSequences(): bool
    {
        return $this->_prefersSequences;
    }

    public function supportsIdentityColumns(): bool
    {
        return $this->_supportsIdentityColumns;
    }

    /**
     * @param mixed $sequenceName
     */
    public function getSequenceNextValSQL($sequenceName): string
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

    public function getName(): string
    {
        return 'mock';
    }

    public function getBlobTypeDeclarationSQL(array $field): void
    {
        throw Exception::notSupported(__METHOD__);
    }

    protected function _getCommonIntegerTypeDeclarationSQL(array $columnDef): void
    {
    }

    protected function initializeDoctrineTypeMappings(): void
    {
    }
}
