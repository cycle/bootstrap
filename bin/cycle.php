#!/usr/bin/env php
<?php
/**
 * Cycle ORM CLI bootstrap.
 */
declare(strict_types=1);

foreach ([
             __DIR__ . '/../../vendor/autoload.php',
             __DIR__ . '/vendor/autoload.php'
         ] as $file) {
    if (file_exists($file)) {
        require_once $file;
        break;
    }
}

unset($file);

$dirs = [getcwd(), getcwd() . DIRECTORY_SEPARATOR . 'config'];

$orm = null;
foreach ($dirs as $directory) {
    $bootFile = $directory . DIRECTORY_SEPARATOR . 'config/cycle-cli.php';
    if (file_exists($bootFile)) {
        $orm = require_once $bootFile;
        break;
    }
}

if (!$orm instanceof \Cycle\ORM\ORMInterface) {
    fwrite(
        STDERR,
        'Unable to find Cycle bootloader in "config/cycle-cli.php", use the given template:

<?php
// replace with file to your own project bootstrap
require_once \'bootstrap.php\';
return $orm;');

    die(1);
}

\Cycle\Bootstrap\App::run($orm);
