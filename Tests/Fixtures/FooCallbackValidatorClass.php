<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensions\Tests\Fixtures;

use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Fixture class for doctrine callback validator.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class FooCallbackValidatorClass
{
    /**
     * Validates static method in class.
     *
     * @param object $object
     *
     * @return bool
     */
    public static function validateCallback($object, ExecutionContextInterface $context)
    {
        $context->addViolation('Callback message', ['{{ value }}' => 'foobar']);

        return false;
    }
}
