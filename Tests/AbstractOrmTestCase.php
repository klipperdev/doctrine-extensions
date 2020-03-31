<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensions\Tests;

use Doctrine\Common\Annotations;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Cache\CacheConfiguration;
use Doctrine\ORM\Cache\DefaultCacheFactory;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Version;
use PHPUnit\Framework\TestCase;

/**
 * Tests case for orm.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
abstract class AbstractOrmTestCase extends TestCase
{
    /**
     * @var bool
     */
    protected $isSecondLevelCacheEnabled = false;

    /**
     * @var \Doctrine\ORM\Cache\CacheFactory
     */
    protected $secondLevelCacheFactory;

    /**
     * @var null|\Doctrine\Common\Cache\Cache
     */
    protected $secondLevelCacheDriverImpl;
    /**
     * The metadata cache that is shared between all ORM tests (except functional tests).
     *
     * @var null|\Doctrine\Common\Cache\Cache
     */
    private static $_metadataCacheImpl = null;

    /**
     * The query cache that is shared between all ORM tests (except functional tests).
     *
     * @var null|\Doctrine\Common\Cache\Cache
     */
    private static $_queryCacheImpl = null;

    /**
     * @param array $paths
     *
     * @return \Doctrine\ORM\Mapping\Driver\AnnotationDriver
     */
    protected function createAnnotationDriver($paths = [])
    {
        if (version_compare(Version::VERSION, '3.0.0', '>=')) {
            $reader = new Annotations\CachedReader(new Annotations\AnnotationReader(), new ArrayCache());
        } else {
            // Register the ORM Annotations in the AnnotationRegistry
            $reader = new Annotations\SimpleAnnotationReader();

            $reader->addNamespace('Doctrine\ORM\Mapping');

            $reader = new Annotations\CachedReader($reader, new ArrayCache());
        }

        Annotations\AnnotationRegistry::registerFile(__DIR__.'/../vendor/doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php');

        return new AnnotationDriver($reader, (array) $paths);
    }

    /**
     * Creates an EntityManager for testing purposes.
     *
     * NOTE: The created EntityManager will have its dependant DBAL parts completely
     * mocked out using a DriverMock, ConnectionMock, etc. These mocks can then
     * be configured in the tests to simulate the DBAL behavior that is desired
     * for a particular test,
     *
     * @param array|\Doctrine\DBAL\Connection    $conn
     * @param null|\Doctrine\Common\EventManager $eventManager
     * @param bool                               $withSharedMetadata
     *
     * @return \Doctrine\ORM\EntityManager
     */
    protected function _getTestEntityManager($conn = null, $eventManager = null, $withSharedMetadata = true)
    {
        $metadataCache = $withSharedMetadata
            ? self::getSharedMetadataCacheImpl()
            : new ArrayCache();

        $config = new Configuration();

        $config->setMetadataCacheImpl($metadataCache);
        $config->setMetadataDriverImpl($config->newDefaultAnnotationDriver([], true));
        $config->setQueryCacheImpl(self::getSharedQueryCacheImpl());
        $config->setProxyDir(__DIR__.'/Proxies');
        $config->setProxyNamespace('Klipper\Component\DoctrineExtensions\Tests\Proxies');
        $config->setMetadataDriverImpl($config->newDefaultAnnotationDriver(
            [
                realpath(__DIR__.'/Models'),
            ],
            true
        ));

        if ($this->isSecondLevelCacheEnabled) {
            $cacheConfig = new CacheConfiguration();
            $cache = $this->getSharedSecondLevelCacheDriverImpl();
            $factory = new DefaultCacheFactory($cacheConfig->getRegionsConfiguration(), $cache);

            $this->secondLevelCacheFactory = $factory;

            $cacheConfig->setCacheFactory($factory);
            $config->setSecondLevelCacheEnabled(true);
            $config->setSecondLevelCacheConfiguration($cacheConfig);
        }

        if (null === $conn) {
            $conn = [
                'driverClass' => Mocks\DriverMock::class,
                'wrapperClass' => Mocks\ConnectionMock::class,
                'user' => 'john',
                'password' => 'doe',
            ];
        }

        if (\is_array($conn)) {
            $conn = DriverManager::getConnection($conn, $config, $eventManager);
        }

        return Mocks\EntityManagerMock::create($conn, $config, $eventManager);
    }

    /**
     * @return \Doctrine\Common\Cache\Cache
     */
    protected function getSharedSecondLevelCacheDriverImpl()
    {
        if (null === $this->secondLevelCacheDriverImpl) {
            $this->secondLevelCacheDriverImpl = new ArrayCache();
        }

        return $this->secondLevelCacheDriverImpl;
    }

    /**
     * @return \Doctrine\Common\Cache\Cache
     */
    private static function getSharedMetadataCacheImpl()
    {
        if (null === self::$_metadataCacheImpl) {
            self::$_metadataCacheImpl = new ArrayCache();
        }

        return self::$_metadataCacheImpl;
    }

    /**
     * @return \Doctrine\Common\Cache\Cache
     */
    private static function getSharedQueryCacheImpl()
    {
        if (null === self::$_queryCacheImpl) {
            self::$_queryCacheImpl = new ArrayCache();
        }

        return self::$_queryCacheImpl;
    }
}
