<?php
define( 'UNIT_TESTS', true );
require_once __DIR__ . '/../src/helpers.php';
putenv( 'QIT_CLI_CONFIG_DIR=/tmp/' );
\QIT_CLI\App::make( \QIT_CLI\Environment::class )->create_environment( 'tests' );