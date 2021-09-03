<?php

/**
 * Cycle ORM CLI bootstrap.
 *
 * @license MIT
 * @author  Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Cycle\Bootstrap\Command;

use Cycle\Annotated;
use Cycle\Bootstrap\Command\Generator\ShowChanges;
use Cycle\Bootstrap\SchemaHandlerInterface;
use Cycle\Schema;
use Cycle\Schema\Registry;
use Spiral\Console\Command;
use Spiral\Tokenizer\ClassesInterface;

final class UpdateCommand extends Command
{
    protected const NAME = 'schema:update';
    protected const DESCRIPTION = 'Update ORM schema based on entity and relation annotations';

    /**
     * @param Registry               $registry
     * @param ClassesInterface       $cl
     * @param SchemaHandlerInterface $handler
     *
     * @throws \Throwable
     */
    public function perform(
        Registry $registry,
        ClassesInterface $cl,
        SchemaHandlerInterface $handler
    ): void {
        $show = new ShowChanges($this->output);

        $schema = (new Schema\Compiler())->compile($registry, [
            new Annotated\Embeddings($cl),
            new Annotated\Entities($cl),
            $this->container->get(Schema\Generator\ResetTables::class),
            $this->container->get(Schema\Generator\GenerateRelations::class),
            $this->container->get(Schema\Generator\ValidateEntities::class),
            $this->container->get(Schema\Generator\RenderTables::class),
            $this->container->get(Schema\Generator\RenderRelations::class),
            $this->container->get(Schema\Generator\GenerateTypecast::class),
            $show,
        ]);

        $schema = new \Cycle\ORM\Schema($schema);
        $handler->handle($schema);

        if (!$show->hasChanges()) {
            $this->writeln("\n<comment>No schema changes were detected</comment>");
        }
    }
}
