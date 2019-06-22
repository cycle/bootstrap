<?php
// Cycle bootstrap
declare(strict_types=1);

require_once "vendor/autoload.php";

$orm = \Cycle\Bootstrap\Bootstrap::fromConfigFile('config/cycle.php');