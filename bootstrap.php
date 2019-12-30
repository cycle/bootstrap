<?php
// Cycle bootstrap
declare(strict_types=1);

use Cycle\Bootstrap\Bootstrap;
use Doctrine\Common\Annotations\AnnotationRegistry;

require_once 'vendor/autoload.php';

AnnotationRegistry::registerLoader('class_exists');

$orm = Bootstrap::fromConfigFile('config/cycle.php');
