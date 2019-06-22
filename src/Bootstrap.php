<?php
/**
 * Cycle ORM
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Cycle\Console;

use Cycle\Annotated;
use Cycle\Console\Exception\BootstrapException;
use Cycle\ORM\Factory;
use Cycle\ORM\ORM;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\Promise\ProxyFactory;
use Cycle\ORM\SchemaInterface;
use Cycle\Schema;
use Psr\Container\ContainerInterface;
use Spiral\Core\Container;
use Spiral\Database\Config\DatabaseConfig;
use Spiral\Database\DatabaseManager;
use Spiral\Database\DatabaseProviderInterface;
use Spiral\Tokenizer\ClassesInterface;
use Spiral\Tokenizer\ClassLocator;
use Symfony\Component\Finder\Finder;

final class Bootstrap
{
    /**
     * @param string $file
     * @return ORMInterface
     */
    public static function fromConfigFile(string $file): ORMInterface
    {
        if (!file_exists($file)) {
            throw new BootstrapException("No such config file {$file}");
        }

        $config = require_once $file;
        if (!$config instanceof Config) {
            throw new BootstrapException("Invalid config file {$file}, expected `Cycle\Standalone\Config`");
        }

        return self::fromConfig($config);
    }

    /**
     * Create ORM instance using provided config. Automatically indexes
     *
     * @param Config $cfg
     * @return ORMInterface
     */
    public static function fromConfig(Config $cfg): ORMInterface
    {
        if ($cfg->getDatabaseConfig() === null) {
            throw new BootstrapException("DatabaseConfig is not set");
        }

        if ($cfg->getEntityDirectory() === null) {
            throw new BootstrapException("Entity directory is not set");
        }

        // we can store some external deps with factory
        $container = new Container();
        $container->bindSingleton(Config::class, $cfg);

        $container->bindSingleton(ClassesInterface::class, new ClassLocator(
            (new Finder())->in([$cfg->getEntityDirectory()])->files()
        ));

        // database provider
        $dbal = new DatabaseManager($cfg->getDatabaseConfig());

        $container->bindSingleton(DatabaseConfig::class, $cfg->getDatabaseConfig());
        $container->bindSingleton(DatabaseProviderInterface::class, $dbal);
        $container->bindSingleton(DatabaseManager::class, $dbal);

        $orm = new ORM(
            new Factory($dbal, null, $container, $container),
            self::bootSchema($cfg, $container)
        );

        $orm = $orm->withPromiseFactory(new ProxyFactory());

        return $orm;
    }

    /**
     * Store schema in cache if cache is set.
     *
     * @param Config          $cfg
     * @param SchemaInterface $schema
     */
    public static function storeSchema(Config $cfg, SchemaInterface $schema)
    {
        if ($cfg->getCacheFile() === null) {
            // nothing to store
            return;
        }

        file_put_contents($cfg->getCacheFile(), '<?php ' . var_export($schema, true));
    }

    /**
     * @param Config             $cfg
     * @param ContainerInterface $container
     * @return SchemaInterface
     */
    protected static function bootSchema(Config $cfg, ContainerInterface $container): SchemaInterface
    {
        if ($cfg->getCacheFile() !== null) {
            if (file_exists($cfg->getCacheFile())) {
                return require_once include $cfg->getCacheFile();
            }
        }

        /** @var Schema\Registry $registry */
        $registry = $container->get(Schema\Registry::class);
        $cl = $container->get(ClassesInterface::class);

        $schema = (new Schema\Compiler())->compile($registry, [
            new Annotated\Embeddings($cl),
            new Annotated\Entities($cl),
            $container->get(Schema\Generator\ResetTables::class),
            $container->get(Schema\Generator\GenerateRelations::class),
            $container->get(Schema\Generator\ValidateEntities::class),
            $container->get(Schema\Generator\RenderTables::class),
            $container->get(Schema\Generator\RenderRelations::class),
            $container->get(Schema\Generator\GenerateTypecast::class),
        ]);

        $schema = new \Cycle\ORM\Schema($schema);
        self::storeSchema($cfg, $schema);

        return $schema;
    }
}