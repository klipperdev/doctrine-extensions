<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensions\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "PROPERTY", "METHOD", "ANNOTATION"})
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class DoctrineCallback extends Constraint
{
    /**
     * @var callable|string
     */
    public $callback;

    /**
     * {@inheritdoc}
     */
    public function __construct($options = null)
    {
        if (\is_array($options)) {
            $this->initArraySingleOption($options);
            $this->initArrayCallbackOption($options);
        }

        parent::__construct($options);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOption(): ?string
    {
        return 'callback';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return [self::CLASS_CONSTRAINT, self::PROPERTY_CONSTRAINT];
    }

    /**
     * Invocation through annotations with an array parameter only.
     */
    protected function initArraySingleOption(array &$options): void
    {
        if (1 === \count($options) && isset($options['value'])) {
            $options = $options['value'];
        }
    }

    /**
     * Init callback options.
     *
     * @param array|callable $options
     */
    protected function initArrayCallbackOption(&$options): void
    {
        if (!isset($options['callback']) && !isset($options['groups']) && \is_callable($options)) {
            $options = ['callback' => $options];
        }
    }
}
