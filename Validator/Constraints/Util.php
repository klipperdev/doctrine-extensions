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

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Klipper\Component\DoctrineExtensions\Exception\ConstraintDefinitionException;
use Klipper\Component\DoctrineExtensions\Exception\UnexpectedTypeException;
use Klipper\Component\DoctrineExtra\Util\ClassUtils;
use Klipper\Component\DoctrineExtra\Util\ManagerUtils;
use Symfony\Component\Validator\Constraint;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class Util
{
    /**
     * Get the formatted identifier.
     *
     * @param ClassMetadata   $relatedClass The metadata of related class
     * @param array           $criteria     The validator criteria
     * @param string          $fieldName    The field name
     * @param null|int|string $value        The identifier value
     *
     * @return int|string
     */
    public static function getFormattedIdentifier(ClassMetadata $relatedClass, array $criteria, string $fieldName, $value)
    {
        $isObject = \is_object($criteria[$fieldName]);

        return $isObject && null === $value
            ? self::formatEmptyIdentifier($relatedClass)
            : $value;
    }

    /**
     * Format the empty identifier value for entity with relation.
     *
     * @param ClassMetadata $meta The class metadata of entity relation
     *
     * @return int|string
     */
    public static function formatEmptyIdentifier(ClassMetadata $meta)
    {
        $type = $meta->getTypeOfField(current($meta->getIdentifier()));

        switch ($type) {
            case 'bigint':
            case 'decimal':
            case 'integer':
            case 'smallint':
            case 'float':
                return 0;
            case 'guid':
                return '00000000-0000-0000-0000-000000000000';
            default:
                return '';
        }
    }

    /**
     * Pre validate entity.
     *
     * @param ManagerRegistry $registry
     * @param object          $entity
     * @param Constraint      $constraint
     *
     * @throws UnexpectedTypeException
     * @throws ConstraintDefinitionException
     *
     * @return ObjectManager
     */
    public static function getObjectManager(ManagerRegistry $registry, $entity, Constraint $constraint): ObjectManager
    {
        self::validateConstraint($constraint);
        /* @var UniqueEntity $constraint */

        return self::findObjectManager($registry, $entity, $constraint);
    }

    /**
     * @param Constraint $constraint
     *
     * @throws UnexpectedTypeException
     * @throws ConstraintDefinitionException
     */
    private static function validateConstraint(Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueEntity) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\UniqueEntity');
        }

        if (!\is_array($constraint->fields) && !\is_string($constraint->fields)) {
            throw new UnexpectedTypeException($constraint->fields, 'array');
        }

        if (null !== $constraint->errorPath && !\is_string($constraint->errorPath)) {
            throw new UnexpectedTypeException($constraint->errorPath, 'string or null');
        }

        if (0 === \count((array) $constraint->fields)) {
            throw new ConstraintDefinitionException('At least one field has to be specified.');
        }
    }

    /**
     * @param ManagerRegistry $registry
     * @param object          $entity
     * @param UniqueEntity    $constraint
     *
     * @throws ConstraintDefinitionException
     *
     * @return ObjectManager
     */
    private static function findObjectManager(ManagerRegistry $registry, $entity, UniqueEntity $constraint): ObjectManager
    {
        if ($constraint->em) {
            $em = $registry->getManager($constraint->em);

            if (!$em) {
                throw new ConstraintDefinitionException(sprintf('Object manager "%s" does not exist.', $constraint->em));
            }
        } else {
            $em = ManagerUtils::getManager($registry, ClassUtils::getClass($entity));

            if (!$em) {
                throw new ConstraintDefinitionException(sprintf('Unable to find the object manager associated with an entity of class "%s".', \get_class($entity)));
            }
        }

        return $em;
    }
}
