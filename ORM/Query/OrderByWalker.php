<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensions\ORM\Query;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\Query\AST\OrderByClause;
use Doctrine\ORM\Query\AST\OrderByItem;
use Doctrine\ORM\Query\AST\PathExpression;
use Doctrine\ORM\Query\AST\SelectStatement;
use Doctrine\ORM\Query\TreeWalkerAdapter;

/**
 * OrderBy Query TreeWalker for Sortable functionality
 * in doctrine paginator.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class OrderByWalker extends TreeWalkerAdapter
{
    /**
     * Sort key alias hint name.
     */
    public const HINT_SORT_ALIAS = 'klipper_paginator.sort.alias';

    /**
     * Sort key field hint name.
     */
    public const HINT_SORT_FIELD = 'klipper_paginator.sort.field';

    /**
     * Sort direction hint name.
     */
    public const HINT_SORT_DIRECTION = 'klipper_paginator.sort.direction';

    /**
     * {@inheritdoc}
     */
    public function walkSelectStatement(SelectStatement $AST): void
    {
        $query = $this->_getQuery();

        // execute a walker for hint with string value
        if (!\is_array($query->getHint(self::HINT_SORT_FIELD))) {
            parent::walkSelectStatement($AST);

            return;
        }

        // execute a walker for hint with array value
        $fields = $query->getHint(self::HINT_SORT_FIELD);
        $aliases = $query->getHint(self::HINT_SORT_ALIAS);
        $directions = $query->getHint(self::HINT_SORT_DIRECTION);
        $components = $this->_getQueryComponents();
        $fieldsSize = \count($fields);

        // init ordering
        $AST->orderByClause = new OrderByClause([]);

        if (!\is_array($aliases) || !\is_array($directions)) {
            throw new \InvalidArgumentException('The HINT_SORT_ALIAS and HINT_SORT_DIRECTION must be an array');
        }

        for ($i = 0; $i < $fieldsSize; ++$i) {
            $field = $fields[$i];
            $alias = $aliases[$i];
            $direction = $directions[$i];

            if (false !== $alias) {
                if (!\array_key_exists($alias, $components)) {
                    throw new \UnexpectedValueException("There is no component aliased by [{$alias}] in the given Query");
                }

                /** @var ClassMetadata $meta */
                $meta = $components[$alias]['metadata'];

                if (!$meta->hasField($field)) {
                    throw new \UnexpectedValueException("There is no such field [{$field}] in the given Query component, aliased by [${alias}]");
                }
            } else {
                if (!\array_key_exists($field, $components)) {
                    throw new \UnexpectedValueException("There is no component field [{$field}] in the given Query");
                }
            }

            $pathExpression = $field;

            if (false !== $alias) {
                $pathExpression = new PathExpression(PathExpression::TYPE_STATE_FIELD, $alias, $field);
                $pathExpression->type = PathExpression::TYPE_STATE_FIELD;
            }

            $orderByItem = new OrderByItem($pathExpression);
            $orderByItem->type = $direction;

            $AST->orderByClause->orderByItems[] = $orderByItem;
        }
    }
}
