<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensions\Tests\Validator\Constraints;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Klipper\Component\DoctrineExtensions\Exception\ConstraintDefinitionException;
use Klipper\Component\DoctrineExtensions\Exception\UnexpectedTypeException;
use Klipper\Component\DoctrineExtensions\Tests\Fixtures\AssociationEntity;
use Klipper\Component\DoctrineExtensions\Tests\Fixtures\BarFilter;
use Klipper\Component\DoctrineExtensions\Tests\Fixtures\CompositeIntIdEntity;
use Klipper\Component\DoctrineExtensions\Tests\Fixtures\DoubleNameEntity;
use Klipper\Component\DoctrineExtensions\Tests\Fixtures\FakeMetadataFactory;
use Klipper\Component\DoctrineExtensions\Tests\Fixtures\FooFilter;
use Klipper\Component\DoctrineExtensions\Tests\Fixtures\SingleIntIdEntity;
use Klipper\Component\DoctrineExtensions\Validator\Constraints\UniqueEntity;
use Klipper\Component\DoctrineExtensions\Validator\Constraints\UniqueEntityValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorFactoryInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Context\ExecutionContextFactory;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\RecursiveValidator;

/**
 * Tests case for unique entity validator.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class UniqueEntityValidatorTest extends TestCase
{
    public function testConstraintIsNotUniqueEntity(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        /** @var ManagerRegistry $registry */
        /** @var Constraint $constraint */
        $entityManagerName = 'foo';
        $em = $this->createTestEntityManager();
        $registry = $this->createRegistryMock($entityManagerName, $em);
        $validator = new UniqueEntityValidator($registry);
        $constraint = $this->getMockForAbstractClass(Constraint::class);
        $entity = new SingleIntIdEntity(1, 'Foo');

        $validator->validate($entity, $constraint);
    }

    public function testConstraintWrongFieldType(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        /** @var ManagerRegistry $registry */
        $entityManagerName = 'foo';
        $em = $this->createTestEntityManager();
        $registry = $this->createRegistryMock($entityManagerName, $em);
        $validator = new UniqueEntityValidator($registry);
        $constraint = new UniqueEntity(['fields' => 42]);
        $entity = new SingleIntIdEntity(1, 'Foo');

        $validator->validate($entity, $constraint);
    }

    public function testConstraintWrongErrorPath(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        /** @var ManagerRegistry $registry */
        $entityManagerName = 'foo';
        $em = $this->createTestEntityManager();
        $registry = $this->createRegistryMock($entityManagerName, $em);
        $validator = new UniqueEntityValidator($registry);
        $constraint = new UniqueEntity(['fields' => 'name', 'errorPath' => 42]);
        $entity = new SingleIntIdEntity(1, 'Foo');

        $validator->validate($entity, $constraint);
    }

    public function testConstraintHasNotField(): void
    {
        $this->expectException(ConstraintDefinitionException::class);

        /** @var ManagerRegistry $registry */
        $entityManagerName = 'foo';
        $em = $this->createTestEntityManager();
        $registry = $this->createRegistryMock($entityManagerName, $em);
        $validator = new UniqueEntityValidator($registry);
        $constraint = new UniqueEntity(['fields' => 'name']);
        $constraint->fields = [];
        $entity = new SingleIntIdEntity(1, 'Foo');

        $validator->validate($entity, $constraint);
    }

    public function testConstraintHasNotExistingField(): void
    {
        $this->expectException(ConstraintDefinitionException::class);

        $entityManagerName = 'foo';
        $em = $this->createTestEntityManager();
        $this->createSchema($em);
        $validator = $this->createValidator($entityManagerName, $em, null, '42');
        $entity1 = new SingleIntIdEntity(1, 'Foo');

        $validator->validate($entity1);
    }

    public function testValidateUniqueness(): void
    {
        $entityManagerName = 'foo';
        $em = $this->createTestEntityManager();
        $this->createSchema($em);
        $validator = $this->createValidator($entityManagerName, $em);

        $entity1 = new SingleIntIdEntity(1, 'Foo');
        $violationsList = $validator->validate($entity1);
        static::assertEquals(0, $violationsList->count(), 'No violations found on entity before it is saved to the database.');

        $em->persist($entity1);
        $em->flush();

        $violationsList = $validator->validate($entity1);
        static::assertEquals(0, $violationsList->count(), 'No violations found on entity after it was saved to the database.');

        $entity2 = new SingleIntIdEntity(2, 'Foo');

        $violationsList = $validator->validate($entity2);
        static::assertEquals(1, $violationsList->count(), 'Violation found on entity with conflicting entity existing in the database.');

        /** @var ConstraintViolationInterface $violation */
        $violation = $violationsList[0];
        static::assertEquals('This value is already used.', $violation->getMessage());
        static::assertEquals('name', $violation->getPropertyPath());
        static::assertEquals('Foo', $violation->getInvalidValue());
    }

    public function testValidateCustomErrorPath(): void
    {
        $entityManagerName = 'foo';
        $em = $this->createTestEntityManager();
        $this->createSchema($em);
        $validator = $this->createValidator($entityManagerName, $em, null, null, 'bar');

        $entity1 = new SingleIntIdEntity(1, 'Foo');

        $em->persist($entity1);
        $em->flush();

        $entity2 = new SingleIntIdEntity(2, 'Foo');

        $violationsList = $validator->validate($entity2);
        static::assertEquals(1, $violationsList->count(), 'Violation found on entity with conflicting entity existing in the database.');

        /** @var ConstraintViolationInterface $violation */
        $violation = $violationsList[0];
        static::assertEquals('This value is already used.', $violation->getMessage());
        static::assertEquals('bar', $violation->getPropertyPath());
        static::assertEquals('Foo', $violation->getInvalidValue());
    }

    public function testValidateUniquenessWithNull(): void
    {
        $entityManagerName = 'foo';
        $em = $this->createTestEntityManager();
        $this->createSchema($em);
        $validator = $this->createValidator($entityManagerName, $em);

        $entity1 = new SingleIntIdEntity(1, null);
        $entity2 = new SingleIntIdEntity(2, null);

        $em->persist($entity1);
        $em->persist($entity2);
        $em->flush();

        $violationsList = $validator->validate($entity1);
        static::assertEquals(0, $violationsList->count(), 'No violations found on entity having a null value.');
    }

    public function testValidateUniquenessWithIgnoreNull(): void
    {
        $entityManagerName = 'foo';
        $validateClass = DoubleNameEntity::class;
        $em = $this->createTestEntityManager();
        $this->createSchema($em);
        $validator = $this->createValidator($entityManagerName, $em, $validateClass, ['name', 'name2'], 'bar', 'findby', false);

        $entity1 = new DoubleNameEntity(1, 'Foo', null);
        $violationsList = $validator->validate($entity1);
        static::assertEquals(0, $violationsList->count(), 'No violations found on entity before it is saved to the database.');

        $em->persist($entity1);
        $em->flush();

        $violationsList = $validator->validate($entity1);
        static::assertEquals(0, $violationsList->count(), 'No violations found on entity after it was saved to the database.');

        $entity2 = new DoubleNameEntity(2, 'Foo', null);

        $violationsList = $validator->validate($entity2);
        static::assertEquals(1, $violationsList->count(), 'Violation found on entity with conflicting entity existing in the database.');

        /** @var ConstraintViolationInterface $violation */
        $violation = $violationsList[0];
        static::assertEquals('This value is already used.', $violation->getMessage());
        static::assertEquals('bar', $violation->getPropertyPath());
        static::assertNull($violation->getInvalidValue());
    }

    public function testValidateUniquenessAfterConsideringMultipleQueryResults(): void
    {
        $entityManagerName = 'foo';
        $em = $this->createTestEntityManager();
        $this->createSchema($em);
        $validator = $this->createValidator($entityManagerName, $em);

        $entity1 = new SingleIntIdEntity(1, 'foo');
        $entity2 = new SingleIntIdEntity(2, 'foo');

        $em->persist($entity1);
        $em->persist($entity2);
        $em->flush();

        $violationsList = $validator->validate($entity1);
        static::assertEquals(1, $violationsList->count(), 'Violation found on entity with conflicting entity existing in the database.');

        $violationsList = $validator->validate($entity2);
        static::assertEquals(1, $violationsList->count(), 'Violation found on entity with conflicting entity existing in the database.');
    }

    public function testValidateUniquenessUsingCustomRepositoryMethod(): void
    {
        $entityManagerName = 'foo';
        $repository = $this->createRepositoryMock();
        $repository->expects(static::once())
            ->method('findByCustom')
            ->willReturn([])
        ;
        $em = $this->createEntityManagerMock($repository);
        $validator = $this->createValidator($entityManagerName, $em, null, [], null, 'findByCustom');

        $entity1 = new SingleIntIdEntity(1, 'foo');

        $violationsList = $validator->validate($entity1);
        static::assertEquals(0, $violationsList->count(), 'Violation is using custom repository method.');
    }

    public function testValidateUniquenessWithUnrewoundArray(): void
    {
        $entity = new SingleIntIdEntity(1, 'foo');

        $entityManagerName = 'foo';
        $repository = $this->createRepositoryMock();
        $repository->expects(static::once())
            ->method('findByCustom')
            ->willReturnCallback(
                function () use ($entity) {
                    $returnValue = [
                        $entity,
                    ];
                    next($returnValue);

                    return $returnValue;
                }
            )
        ;
        $em = $this->createEntityManagerMock($repository);
        $validator = $this->createValidator($entityManagerName, $em, null, [], null, 'findByCustom');

        $violationsList = $validator->validate($entity);
        static::assertCount(0, $violationsList, 'Violation is using unrewound array as return value in the repository method.');
    }

    public function testAssociatedEntity(): void
    {
        $entityManagerName = 'foo';
        $em = $this->createTestEntityManager();
        $this->createSchema($em);
        $validator = $this->createValidator($entityManagerName, $em, AssociationEntity::class, ['single']);

        $entity1 = new SingleIntIdEntity(1, 'foo');
        $associated = new AssociationEntity();
        $associated->single = $entity1;

        $em->persist($entity1);
        $em->persist($associated);
        $em->flush();

        $violationsList = $validator->validate($associated);
        static::assertEquals(0, $violationsList->count());

        $associated2 = new AssociationEntity();
        $associated2->single = $entity1;

        $em->persist($associated2);
        $em->flush();

        $violationsList = $validator->validate($associated2);
        static::assertEquals(1, $violationsList->count());
    }

    public function testAssociatedEntityWithNull(): void
    {
        $entityManagerName = 'foo';
        $em = $this->createTestEntityManager();
        $this->createSchema($em);
        $validator = $this->createValidator($entityManagerName, $em, AssociationEntity::class, ['single'], null, 'findBy', false);

        $associated = new AssociationEntity();
        $associated->single = null;

        $em->persist($associated);
        $em->flush();

        $violationsList = $validator->validate($associated);
        static::assertEquals(0, $violationsList->count());
    }

    public function testAssociatedCompositeEntity(): void
    {
        $this->expectException(\Symfony\Component\Validator\Exception\ConstraintDefinitionException::class);
        $this->expectExceptionMessage('Associated entities are not allowed to have more than one identifier field');

        $entityManagerName = 'foo';
        $em = $this->createTestEntityManager();
        $this->createSchema($em);
        $validator = $this->createValidator($entityManagerName, $em, AssociationEntity::class, ['composite']);

        $composite = new CompositeIntIdEntity(1, 1, 'test');
        $associated = new AssociationEntity();
        $associated->composite = $composite;

        $em->persist($composite);
        $em->persist($associated);
        $em->flush();

        $validator->validate($associated);
    }

    public function testDedicatedEntityManagerNullObject(): void
    {
        $this->expectException(\Symfony\Component\Validator\Exception\ConstraintDefinitionException::class);
        $this->expectExceptionMessage('Object manager "foo" does not exist.');

        $uniqueFields = ['name'];
        $entityManagerName = 'foo';

        /** @var ManagerRegistry $registry */
        $registry = $this->getMockBuilder(ManagerRegistry::class)->getMock();

        $constraint = new UniqueEntity([
            'fields' => $uniqueFields,
            'em' => $entityManagerName,
        ]);

        $uniqueValidator = new UniqueEntityValidator($registry);

        $entity = new SingleIntIdEntity(1, null);

        $uniqueValidator->validate($entity, $constraint);
    }

    public function testEntityManagerNullObject(): void
    {
        $this->expectException(\Symfony\Component\Validator\Exception\ConstraintDefinitionException::class);
        $this->expectExceptionMessage('Unable to find the object manager associated with an entity of class "Klipper\\Component\\DoctrineExtensions\\Tests\\Fixtures\\SingleIntIdEntity"');

        $uniqueFields = ['name'];

        /** @var ManagerRegistry|MockObject $registry */
        $registry = $this->getMockBuilder(ManagerRegistry::class)->getMock();

        $registry->expects(static::once())
            ->method('getManagers')
            ->willReturn([])
        ;

        $constraint = new UniqueEntity([
            'fields' => $uniqueFields,
        ]);

        $uniqueValidator = new UniqueEntityValidator($registry);

        $entity = new SingleIntIdEntity(1, null);

        $uniqueValidator->validate($entity, $constraint);
    }

    public function testDisableAllFilterAndReactivateAfter(): void
    {
        $entityManagerName = 'foo';
        $em = $this->createTestEntityManager();
        $em->getConfiguration()->addFilter('fooFilter1', FooFilter::class);
        $em->getConfiguration()->addFilter('fooFilter2', FooFilter::class);
        $em->getConfiguration()->addFilter('barFilter1', BarFilter::class);
        $em->getFilters()->enable('fooFilter1');
        $em->getFilters()->enable('fooFilter2');
        $em->getFilters()->enable('barFilter1');
        $this->createSchema($em);
        $validator = $this->createValidator($entityManagerName, $em);
        $entity1 = new SingleIntIdEntity(1, 'Foo');

        static::assertCount(3, $em->getFilters()->getEnabledFilters());

        $violationsList = $validator->validate($entity1);

        static::assertEquals(0, $violationsList->count());
        static::assertCount(3, $em->getFilters()->getEnabledFilters());
    }

    public function testDisableOneFilterAndReactivateAfter(): void
    {
        $entityManagerName = 'foo';
        $em = $this->createTestEntityManager();
        $em->getConfiguration()->addFilter('fooFilter1', FooFilter::class);
        $em->getConfiguration()->addFilter('fooFilter2', FooFilter::class);
        $em->getConfiguration()->addFilter('barFilter1', BarFilter::class);
        $em->getFilters()->enable('fooFilter1');
        $em->getFilters()->enable('fooFilter2');
        $em->getFilters()->enable('barFilter1');
        $this->createSchema($em);
        $validator = $this->createValidator($entityManagerName, $em, null, null, null, 'findBy', true, ['fooFilter1'], false);
        $entity1 = new SingleIntIdEntity(1, 'Foo');

        static::assertCount(3, $em->getFilters()->getEnabledFilters());

        $violationsList = $validator->validate($entity1);

        static::assertEquals(0, $violationsList->count());
        static::assertCount(3, $em->getFilters()->getEnabledFilters());
    }

    protected function createRegistryMock(string $entityManagerName, ObjectManager $em)
    {
        $registry = $this->getMockBuilder(ManagerRegistry::class)->getMock();
        $registry->expects(static::any())
            ->method('getManager')
            ->with(static::equalTo($entityManagerName))
            ->willReturn($em)
        ;

        return $registry;
    }

    protected function createRepositoryMock()
    {
        return $this->getMockBuilder(ObjectRepository::class)
            ->setMethods(['findByCustom', 'find', 'findAll', 'findOneBy', 'findBy', 'getClassName'])
            ->getMock()
        ;
    }

    protected function createEntityManagerMock($repositoryMock): ObjectManager
    {
        $em = $this->getMockBuilder(ObjectManager::class)
            ->getMock()
        ;
        $em->expects(static::any())
            ->method('getRepository')
            ->willReturn($repositoryMock)
        ;

        $classMetadata = $this->getMockBuilder(\Doctrine\Persistence\Mapping\ClassMetadata::class)->getMock();
        $classMetadata
            ->expects(static::any())
            ->method('hasField')
            ->willReturn(true)
        ;
        $refl = $this->getMockBuilder(\ReflectionProperty::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMock()
        ;
        $refl
            ->expects(static::any())
            ->method('getValue')
            ->willReturn(true)
        ;
        /* @var \Doctrine\ORM\Mapping\ClassMetadata $classMetadata */
        $classMetadata->reflFields = ['name' => $refl];
        $em->expects(static::any())
            ->method('getClassMetadata')
            ->willReturn($classMetadata)
        ;

        return $em;
    }

    protected function createValidatorFactory($uniqueValidator)
    {
        $validatorFactory = $this->getMockBuilder(ConstraintValidatorFactoryInterface::class)->getMock();
        $validatorFactory->expects(static::any())
            ->method('getInstance')
            ->with(static::isInstanceOf(UniqueEntity::class))
            ->willReturn($uniqueValidator)
        ;

        return $validatorFactory;
    }

    protected function createValidator(string $entityManagerName, ObjectManager $em, ?string $validateClass = null, $uniqueFields = null, $errorPath = null, $repositoryMethod = 'findBy', $ignoreNull = true, array $filters = [], $all = true)
    {
        if (!$validateClass) {
            $validateClass = SingleIntIdEntity::class;
        }
        if (!$uniqueFields) {
            $uniqueFields = ['name'];
        }

        /** @var ManagerRegistry $registry */
        $registry = $this->createRegistryMock($entityManagerName, $em);

        $uniqueValidator = new UniqueEntityValidator($registry);

        $metadata = new ClassMetadata($validateClass);
        $constraint = new UniqueEntity([
            'fields' => $uniqueFields,
            'em' => $entityManagerName,
            'errorPath' => $errorPath,
            'repositoryMethod' => $repositoryMethod,
            'ignoreNull' => $ignoreNull,
            'filters' => $filters,
            'allFilters' => $all,
        ]);
        $metadata->addConstraint($constraint);

        $metadataFactory = new FakeMetadataFactory();
        $metadataFactory->addMetadata($metadata);
        /** @var ConstraintValidatorFactoryInterface $validatorFactory */
        $validatorFactory = $this->createValidatorFactory($uniqueValidator);
        $contextFactory = new ExecutionContextFactory(new IdentityTranslator(), null);

        return new RecursiveValidator($contextFactory, $metadataFactory, $validatorFactory, []);
    }

    protected function createSchema(EntityManagerInterface $em): void
    {
        $schemaTool = new SchemaTool($em);
        $schemaTool->createSchema([
            $em->getClassMetadata(SingleIntIdEntity::class),
            $em->getClassMetadata(DoubleNameEntity::class),
            $em->getClassMetadata(CompositeIntIdEntity::class),
            $em->getClassMetadata(AssociationEntity::class),
        ]);
    }

    private function createTestEntityManager(): EntityManager
    {
        if (!\extension_loaded('pdo_sqlite')) {
            TestCase::markTestSkipped('Extension pdo_sqlite is required.');
        }

        $config = new Configuration();
        $config->setEntityNamespaces(['SymfonyTestsDoctrine' => 'Symfony\Bridge\Doctrine\Tests\Fixtures']);
        $config->setAutoGenerateProxyClasses(true);
        $config->setProxyDir(sys_get_temp_dir());
        $config->setProxyNamespace('SymfonyTests\Doctrine');
        $config->setMetadataDriverImpl(new AnnotationDriver(new AnnotationReader()));

        $params = [
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ];

        return EntityManager::create($params, $config);
    }
}
