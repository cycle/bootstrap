<?php

/**
 * Cycle ORM CLI bootstrap.
 *
 * @license MIT
 * @author  Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Cycle\Bootstrap;

use Cycle\Bootstrap\Command\Database;
use Cycle\Bootstrap\Command\ListCommand;
use Cycle\Bootstrap\Command\SyncCommand;
use Cycle\Bootstrap\Command\UpdateCommand;
use Cycle\ORM\ORMInterface;
use Spiral\Console\Config\ConsoleConfig;
use Spiral\Console\Console;
use Spiral\Core\ScopeInterface;
use Throwable;

final class App
{
    /**
     * @param ORMInterface $orm
     *
     * @throws Throwable
     */
    public static function run(ORMInterface $orm): void
    {
        /** @var Console $cli */
        $cli = $orm->getFactory()->make(Console::class, [
            'config' => new ConsoleConfig([
                'name' => 'Cycle Console Toolkit',
                'commands' => [
                    Database\ListCommand::class,
                    Database\TableCommand::class,
                    UpdateCommand::class,
                    SyncCommand::class,
                    ListCommand::class,
                ],
            ]),
        ]);

        /** @var ScopeInterface $scope */
        $scope = $orm->getFactory()->make(ScopeInterface::class);

        $scope->runScope([
            ORMInterface::class => $orm,
        ], static function () use ($cli): void {
            $cli->start();
        });
    }
}
