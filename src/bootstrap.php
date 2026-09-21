<?php

declare(strict_types=1);

require_once __DIR__.'/Config.php';
require_once __DIR__.'/Database.php';
require_once __DIR__.'/Logger.php';
require_once __DIR__.'/Auth.php';

$config = Config::load();
$pdo = Database::connect($config);
Database::migrate($pdo);
Database::seedIfEmpty($pdo);
$logger = new Logger($pdo);
