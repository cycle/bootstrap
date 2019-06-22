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
use Cycle\ORM\ORMInterface;
use Spiral\Console\Config\ConsoleConfig;

final class Console
{
    /**
     * @param ORMInterface $orm
     * @throws \Throwable
     */
    public static function run(ORMInterface $orm)
    {
        /** @var \Spiral\Console\Console $cli */
        $cli = $orm->getFactory()->make(\Spiral\Console\Console::class, [
            'config' => new ConsoleConfig([
                'name'     => 'Cycle Console Toolkit',
                'commands' => [
                    Database\ListCommand::class,
                    Database\TableCommand::class
                ]
            ])
        ]);

        $cli->start();
    }
}