<?php
/**
 * Cycle ORM
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Cycle\Standalone;

use Cycle\ORM\Factory;
use Cycle\ORM\ORM;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\Schema;
use Cycle\Standalone\Exception\BootstrapException;
use Spiral\Core\Container;
use Spiral\Database\DatabaseManager;
use Spiral\Tokenizer\ClassesInterface;
use Spiral\Tokenizer\ClassLocator;
use Symfony\Component\Finder\Finder;

final class Bootstrap
{
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

        // database provider
        $dbal = new DatabaseManager($cfg->getDatabaseConfig());

        // we can store some external deps with factory
        $container = new Container();
        $container->bindSingleton(ClassesInterface::class, new ClassLocator(
            (new Finder())->in([$cfg->getEntityDirectory()])->files()
        ));

        // load cached schema
        $schema = null;
        if ($cfg->getCacheFile() !== null) {
            $schemaCache = include $cfg->getCacheFile();
            if (is_array($schemaCache)) {
                $schema = new Schema($schemaCache);
            }
        }

        $orm = new ORM(
            new Factory(
                $dbal,
                null,
                $container,
                $container
            ),
            $schema
        );

        return $orm;
    }
}