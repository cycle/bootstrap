<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

// For single database
$config = Cycle\Console\Config::forDatabase(
    'sqlite:database.db',
    '',
    ''
);

// which directory contains our entities
$config = $config->withEntityDirectory(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src');

// log all SQL messages to STDERR
$config = $config->withLogger(new \Cycle\Console\StderrLogger(true));

return $config;