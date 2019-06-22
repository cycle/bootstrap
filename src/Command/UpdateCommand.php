<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Cycle\Console\Command;

use Cycle\Annotated;
use Cycle\Console\Bootstrap;
use Cycle\Console\Command\Generator\ShowChanges;
use Cycle\Console\Config;
use Cycle\Schema;
use Cycle\Schema\Registry;
use Psr\Container\ContainerInterface;
use Spiral\Console\Command;
use Spiral\Tokenizer\ClassesInterface;

final class UpdateCommand extends Command
{
    public const NAME        = 'schema:update';
    public const DESCRIPTION = 'Update ORM schema based on entity and relation annotations';

    /**
     * @param ContainerInterface $container
     * @param Config             $cfg
     * @param Registry           $registry
     * @param ClassesInterface   $cl
     */
    public function perform(
        ContainerInterface $container,
        Config $cfg,
        Registry $registry,
        ClassesInterface $cl
    ) {
        $show = new ShowChanges($this->output);

        $schema = (new Schema\Compiler())->compile($registry, [
            new Annotated\Embeddings($cl),
            new Annotated\Entities($cl),
            $container->get(Schema\Generator\ResetTables::class),
            $container->get(Schema\Generator\GenerateRelations::class),
            $container->get(Schema\Generator\ValidateEntities::class),
            $container->get(Schema\Generator\RenderTables::class),
            $container->get(Schema\Generator\RenderRelations::class),
            $container->get(Schema\Generator\GenerateTypecast::class),
            $show,
        ]);

        $schema = new \Cycle\ORM\Schema($schema);
        Bootstrap::storeSchema($cfg, $schema);

        if (!$show->hasChanges()) {
            $this->writeln("\n<comment>No schema changes were detected</comment>");
        }
    }
}