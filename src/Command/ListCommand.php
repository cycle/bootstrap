<?php

/**
 * Cycle ORM CLI bootstrap.
 *
 * @license MIT
 * @author  Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Cycle\Bootstrap\Command;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\Schema;
use Cycle\ORM\SchemaInterface;
use Spiral\Console\Command;

final class ListCommand extends Command
{
    protected const NAME = 'entity:list';
    protected const DESCRIPTION = 'List of all available entities and their tables';

    /**
     * @param ORMInterface $orm
     */
    public function perform(ORMInterface $orm): void
    {
        $grid = $this->table([
            'Role:',
            'Class:',
            'Table:',
            'Repository:',
            'Fields:',
            'Relations:',
        ]);

        if ($orm->getSchema()->getRoles() === []) {
            $this->sprintf('<info>No entity were found</info>');
            return;
        }

        foreach ($orm->getSchema()->getRoles() as $role) {
            $grid->addRow($this->describeEntity($orm->getSchema(), $role));
        }

        $grid->render();
    }

    /**
     * @param SchemaInterface $schema
     * @param string          $role
     *
     * @return array
     */
    protected function describeEntity(SchemaInterface $schema, string $role): array
    {
        return [
            $role,
            $schema->define($role, Schema::ENTITY),
            $schema->define($role, Schema::TABLE),
            $schema->define($role, Schema::REPOSITORY),
            implode(', ', array_keys($schema->define($role, Schema::COLUMNS))),
            implode(', ', array_keys($schema->define($role, Schema::RELATIONS))),
        ];
    }
}
