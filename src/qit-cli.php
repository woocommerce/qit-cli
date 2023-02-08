<?php

use lucatume\DI52\Container;
use QIT_CLI\App;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/helpers.php';

// Initialize DI container.
$container = new Container();
App::setContainer( $container );

$application = require_once __DIR__ . '/src/bootstrap.php';

// Handle CLI request.
$application->run();
