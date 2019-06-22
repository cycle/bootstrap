<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Cycle\Console;

use Cycle\Console\Command\Database;
use Cycle\Console\Command\ListCommand;
use Cycle\Console\Command\SyncCommand;
use Cycle\Console\Command\UpdateCommand;
use Cycle\ORM\ORMInterface;
use Spiral\Console\Config\ConsoleConfig;
use Spiral\Console\Console;
use Spiral\Core\ScopeInterface;

final class App
{
    /**
     * @param ORMInterface $orm
     * @throws \Throwable
     */
    public static function run(ORMInterface $orm)
    {
        /** @var \Spiral\Console\Console $cli */
        $cli = $orm->getFactory()->make(Console::class, [
            'config' => new ConsoleConfig([
                'name'     => 'Cycle Console Toolkit',
                'commands' => [
                    Database\ListCommand::class,
                    Database\TableCommand::class,
                    UpdateCommand::class,
                    SyncCommand::class,
                    ListCommand::class,
                ]
            ])
        ]);

        /** @var ScopeInterface $scope */
        $scope = $orm->getFactory()->make(ScopeInterface::class);

        $scope->runScope([
            ORMInterface::class => $orm
        ], function () use ($cli) {
            $cli->start();
        });
    }
}