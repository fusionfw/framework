<?php

// Load bootstrap
require_once __DIR__ . '/../bootstrap.php';

// Create and run application
use Fusion\Application;

$app = Application::boot();
$app->run();
