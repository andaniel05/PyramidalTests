<?php
declare(strict_types=1);

require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/src/DSL/TDD.php';

define('ROOT_DIR', __DIR__);

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();