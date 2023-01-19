<?php
define( 'UNIT_TESTS', true );
require_once __DIR__ . '/../src/helpers.php';
\QIT_CLI\App::setVar( 'override_cd_config_file', __DIR__ . '/.test_config' );
