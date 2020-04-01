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
use Klipper\Component\DoctrineExtensions\Util\SqlFilterUtil;
use Klipper\Component\DoctrineExtra\Util\ClassUtils;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Unique Entity Validator checks if one or a set of fields contain unique values.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class UniqueEntityValidator extends ConstraintValidator
{
    /**
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * Constructor.
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Validate.
     *
     * @param object $entity
     *
     * @throws UnexpectedTypeException
     * @throws ConstraintDefinitionException
     */
    public function validate($entity, Constraint $constraint): void
    {
        /** @var UniqueEntity $constraint */
        $em = Util::getObjectManager($this->registry, $entity, $constraint);
        $fields = (array) $constraint->fields;
        $criteria = $this->getCriteria($entity, $constraint, $em);

        if (null === $criteria) {
            return;
        }

        $result = $this->getResult($entity, $constraint, $criteria, $em);

        if (!$this->isValidResult($result, $entity)) {
            $errorPath = $constraint->errorPath ?? $fields[0];
            $invalidValue = $criteria[$errorPath] ?? $criteria[$fields[0]];

            $this->context->buildViolation($constraint->message)
                ->atPath($errorPath)
                ->setInvalidValue($invalidValue)
                ->addViolation()
            ;
        }
    }

    /**
     * Gets criteria.
     *
     * @param object $entity
     *
     * @throws ConstraintDefinitionException
     *
     * @return null|array Null if there is no constraint
     */
    protected function getCriteria($entity, Constraint $constraint, ObjectManager $em): ?array
    {
        /** @var UniqueEntity $constraint */
        /** @var \Doctrine\ORM\Mapping\ClassMetadata $class */
        $class = $em->getClassMetadata(ClassUtils::getClass($entity));
        $fields = (array) $constraint->fields;
        $criteria = [];

        foreach ($fields as $fieldName) {
            $criteria = $this->findFieldCriteria($criteria, $constraint, $em, $class, $entity, $fieldName);

            if (null === $criteria) {
                break;
            }
        }

        return $criteria;
    }

    /**
     * Get entity result.
     *
     * @param object $entity
     */
    private function getResult($entity, Constraint $constraint, array $criteria, ObjectManager $em): array
    {
        /** @var UniqueEntity $constraint */
        $filters = SqlFilterUtil::findFilters($em, (array) $constraint->filters, $constraint->allFilters);

        SqlFilterUtil::disableFilters($em, $filters);
        $repository = $em->getRepository(ClassUtils::getClass($entity));
        $result = $repository->{$constraint->repositoryMethod}($criteria);
        SqlFilterUtil::enableFilters($em, $filters);

        if (\is_array($result)) {
            reset($result);
        }

        return $result;
    }

    /**
     * Check if the result is valid.
     *
     * If no entity matched the query criteria or a single entity matched,
     * which is the same as the entity being validated, the criteria is
     * unique.
     *
     * @param array|\Iterator $result
     * @param object          $entity
     */
    private function isValidResult($result, $entity): bool
    {
        return 0 === \count($result)
            || (1 === \count($result)
                && $entity === ($result instanceof \Iterator ? $result->current() : current($result)));
    }

    /**
     * @param object $entity
     *
     * @throws ConstraintDefinitionException
     *
     * @return null|array The new criteria
     */
    private function findFieldCriteria(array $criteria, Constraint $constraint, ObjectManager $em, ClassMetadata $class, $entity, string $fieldName): ?array
    {
        $this->validateFieldCriteria($class, $fieldName);

        /* @var \Doctrine\ORM\Mapping\ClassMetadata $class */
        $criteria[$fieldName] = $class->reflFields[$fieldName]->getValue($entity);

        /** @var UniqueEntity $constraint */
        if ($constraint->ignoreNull && null === $criteria[$fieldName]) {
            $criteria = null;
        } else {
            $this->findFieldCriteriaStep2($criteria, $em, $class, $fieldName);
        }

        return $criteria;
    }

    /**
     * @throws ConstraintDefinitionException
     */
    private function validateFieldCriteria(ClassMetadata $class, string $fieldName): void
    {
        if (!$class->hasField($fieldName) && !$class->hasAssociation($fieldName)) {
            throw new ConstraintDefinitionException(sprintf("The field '%s' is not mapped by Doctrine, so it cannot be validated for uniqueness.", $fieldName));
        }
    }

    /**
     * Finds the criteria for the entity field.
     *
     * @throws ConstraintDefinitionException
     */
    private function findFieldCriteriaStep2(array &$criteria, ObjectManager $em, ClassMetadata $class, string $fieldName): void
    {
        if (null !== $criteria[$fieldName] && $class->hasAssociation($fieldName)) {
            /* Ensure the Proxy is initialized before using reflection to
             * read its identifiers. This is necessary because the wrapped
             * getter methods in the Proxy are being bypassed.
             */
            $em->initializeObject($criteria[$fieldName]);

            $relatedClass = $em->getClassMetadata($class->getAssociationTargetClass($fieldName));
            $relatedId = $relatedClass->getIdentifierValues($criteria[$fieldName]);

            if (\count($relatedId) > 1) {
                throw new ConstraintDefinitionException(
                    'Associated entities are not allowed to have more than one identifier field to be '.
                    'part of a unique constraint in: '.$class->getName().'#'.$fieldName
                );
            }

            $value = array_pop($relatedId);
            $criteria[$fieldName] = Util::getFormattedIdentifier($relatedClass, $criteria, $fieldName, $value);
        }
    }
}
