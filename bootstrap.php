<?php
// Cycle bootstrap
declare(strict_types=1);

use Doctrine\Common\Annotations\AnnotationRegisty;

require_once 'vendor/autoload.php';

AnnotationRegistry::registerLoader('class_exists');

$orm = \Cycle\Bootstrap\Bootstrap::fromConfigFile('config/cycle.php');
