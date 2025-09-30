<?php

// Load bootstrap
require_once __DIR__ . '/../bootstrap.php';

// Create and run application
use Fusion\Core\Application;

$app = new Application();
$app->run();
