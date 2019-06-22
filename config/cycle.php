<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

// For single database
$config = Cycle\Bootstrap\Config::forDatabase(
    'sqlite:database.db',
    '',
    ''
);

// which directory contains our entities
$config = $config->withEntityDirectory(__DIR__ . DIRECTORY_SEPARATOR);

// log all SQL messages to STDERR
$config = $config->withLogger(new \Cycle\Bootstrap\StderrLogger(true));

// enable schema cache (use /vendor/bin/cycle schema:update to flush cache)
//$config = $config->withCacheDirectory(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'cache');

return $config;