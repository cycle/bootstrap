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
return $config->withEntityDirectory(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src');