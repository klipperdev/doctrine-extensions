<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensions\Exception;

use Symfony\Component\Validator\Exception\ConstraintDefinitionException as BaseConstraintDefinitionException;

/**
 * Base ConstraintDefinitionException for the doctrine extensions component.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class ConstraintDefinitionException extends BaseConstraintDefinitionException implements ExceptionInterface
{
}
