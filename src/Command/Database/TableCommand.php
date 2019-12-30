<?php

/**
 * Cycle ORM CLI bootstrap.
 *
 * @license MIT
 * @author  Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Cycle\Bootstrap\Command\Database;

use DateTimeInterface;
use Spiral\Console\Command;
use Spiral\Database\Database;
use Spiral\Database\DatabaseManager;
use Spiral\Database\Exception\DBALException;
use Spiral\Database\Injection\FragmentInterface;
use Spiral\Database\Schema\AbstractColumn;
use Spiral\Database\Schema\AbstractTable;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

final class TableCommand extends Command
{
    protected const NAME        = 'db:table';
    protected const DESCRIPTION = 'Describe table schema of specific database';
    protected const ARGUMENTS = [
        ['table', InputArgument::REQUIRED, 'Table name']
    ];
    protected const OPTIONS   = [
        ['database', 'db', InputOption::VALUE_OPTIONAL, 'Source database', null]
    ];

    /**
     * No information available placeholder.
     */
    private const SKIP = '<comment>---</comment>';

    /**
     * @param DatabaseManager $dbal
     */
    public function perform(DatabaseManager $dbal): void
    {
        $database = $dbal->database($this->option('database'));
        $schema = $database->table($this->argument('table'))->getSchema();

        if (!$schema->exists()) {
            throw new DBALException(
                "Table {$database->getName()}.{$this->argument('table')} does not exists."
            );
        }

        $this->sprintf(
            "\n<fg=cyan>Columns of </fg=cyan><comment>%s.%s</comment>:\n",
            $database->getName(),
            $this->argument('table')
        );

        $this->describeColumns($schema);

        if (!empty($indexes = $schema->getIndexes())) {
            $this->describeIndexes($database, $indexes);
        }

        if (!empty($foreignKeys = $schema->getForeignKeys())) {
            $this->describeForeignKeys($database, $foreignKeys);
        }

        $this->write("\n");
    }

    /**
     * @param AbstractTable $schema
     */
    protected function describeColumns(AbstractTable $schema): void
    {
        $columnsTable = $this->table([
            'Column:',
            'Database Type:',
            'Abstract Type:',
            'PHP Type:',
            'Default Value:'
        ]);

        foreach ($schema->getColumns() as $column) {
            $name = $column->getName();

            if (in_array($column->getName(), $schema->getPrimaryKeys(), true)) {
                $name = "<fg=magenta>{$name}</fg=magenta>";
            }

            $columnsTable->addRow([
                $name,
                $this->describeType($column),
                $this->describeAbstractType($column),
                $column->getType(),
                $this->describeDefaultValue($column) ?: self::SKIP
            ]);
        }

        $columnsTable->render();
    }

    /**
     * @param Database $database
     * @param array    $indexes
     */
    protected function describeIndexes(Database $database, array $indexes): void
    {
        $this->sprintf(
            "\n<fg=cyan>Indexes of </fg=cyan><comment>%s.%s</comment>:\n",
            $database->getName(),
            $this->argument('table')
        );

        $indexesTable = $this->table(['Name:', 'Type:', 'Columns:']);
        foreach ($indexes as $index) {
            $indexesTable->addRow([
                $index->getName(),
                $index->isUnique() ? 'UNIQUE INDEX' : 'INDEX',
                implode(', ', $index->getColumns())
            ]);
        }

        $indexesTable->render();
    }

    /**
     * @param Database $database
     * @param array    $foreignKeys
     */
    protected function describeForeignKeys(Database $database, array $foreignKeys): void
    {
        $this->sprintf(
            "\n<fg=cyan>Foreign Keys of </fg=cyan><comment>%s.%s</comment>:\n",
            $database->getName(),
            $this->argument('table')
        );
        $foreignTable = $this->table([
            'Name:',
            'Column:',
            'Foreign Table:',
            'Foreign Column:',
            'On Delete:',
            'On Update:'
        ]);

        foreach ($foreignKeys as $reference) {
            $foreignTable->addRow([
                $reference->getName(),
                $reference->getColumn(),
                $reference->getForeignTable(),
                $reference->getForeignKey(),
                $reference->getDeleteRule(),
                $reference->getUpdateRule()
            ]);
        }

        $foreignTable->render();
    }

    /**
     * @param AbstractColumn $column
     * @return string|null
     */
    protected function describeDefaultValue(AbstractColumn $column): ?string
    {
        $defaultValue = $column->getDefaultValue();

        if ($defaultValue instanceof FragmentInterface) {
            $defaultValue = "<info>{$defaultValue}</info>";
        }

        if ($defaultValue instanceof DateTimeInterface) {
            $defaultValue = $defaultValue->format('c');
        }

        return $defaultValue;
    }

    /**
     * @param AbstractColumn $column
     * @return string
     */
    private function describeType(AbstractColumn $column): string
    {
        $type = $column->getType();

        $abstractType = $column->getAbstractType();

        if ($column->getSize()) {
            $type .= " ({$column->getSize()})";
        }

        if ($abstractType === 'decimal') {
            $type .= " ({$column->getPrecision()}, {$column->getScale()})";
        }

        return $type;
    }

    /**
     * @param AbstractColumn $column
     * @return string
     */
    private function describeAbstractType(AbstractColumn $column): string
    {
        $abstractType = $column->getAbstractType();

        if (in_array($abstractType, ['primary', 'bigPrimary'])) {
            $abstractType = "<fg=magenta>{$abstractType}</fg=magenta>";
        }

        return $abstractType;
    }
}
