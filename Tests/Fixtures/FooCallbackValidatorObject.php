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
 * Fixture object for doctrine callback validator.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class FooCallbackValidatorObject
{
    /**
     * Validates method in object instance.
     *
     * @return bool
     */
    public function validate(ExecutionContextInterface $context)
    {
        $context->addViolation('My message', ['{{ value }}' => 'foobar']);

        return false;
    }

    /**
     * Validates static method in object instance.
     *
     * @param $object
     *
     * @return bool
     */
    public static function validateStatic($object, ExecutionContextInterface $context)
    {
        $context->addViolation('Static message', ['{{ value }}' => 'baz']);

        return false;
    }
}
