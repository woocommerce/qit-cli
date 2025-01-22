<?php

namespace QIT_CLI_Tests\LocalTests;

use QIT_CLI\App;
use QIT_CLI\LocalTests\PrepareQMLog;
use QIT_CLI_Tests\QITTestCase;
use Spatie\Snapshots\MatchesSnapshots;

class PrepareQMLogTest extends QITTestCase {
	use MatchesSnapshots;

	public function test_parses_fatal_and_parse_errors() {
		$debug_log = <<<'TXT'
[10-Jan-2025 17:12:43 UTC] PHP Parse error:  syntax error, unexpected '|', expecting variable (T_VARIABLE) in /var/www/html/wp-content/plugins/foo-slug/vendor/phpunit/phpunit/src/Framework/Assert/Functions.php on line 83
[10-Jan-2025 17:12:43 UTC] PHP Fatal error:  Exception thrown without a stack frame in Unknown on line 0
[10-Jan-2025 17:52:22 UTC] PHP Fatal error:  Uncaught Exception: Foo in /var/www/html/wp-content/plugins/foo-slug/foo-slug.php:29
TXT;
		$sut = App::make( PrepareQMLog::class );

		$this->assertMatchesJsonSnapshot( $sut->extract_error_info( explode( "\n", $debug_log ) ) );
	}
}