<?php
return [
	'php'                  => '8.2',
	'wp'                   => 'rc',
	'woo'                  => 'rc',
	/*
	 * PHP 8.2 generates thousands of notices in the debug log.
	 * These notices are not consistent between runs, you can
	 * run two tests with the exact same configs and they will
	 * produce slightly different error counts, so we remove
	 * debug_log from this test.
	 */
	'remove_from_snapshot' => 'debug_log',
];