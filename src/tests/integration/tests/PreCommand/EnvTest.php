<?php

namespace integration\tests\PreCommand;

use QIT\SelfTests\CustomTests\Traits\SnapshotHelpers;

class EnvTest extends \PHPUnit\Framework\TestCase {
	use SnapshotHelpers;

	public function test_env_up() {
		$output = qit_precommand( [ 'env:up' ], <<<'JSON'
{
  "sut": {
    "type": "plugin",
    "slug": "test-plugin",
    "source": { "type": "local", "path": "./" }
  },
  "environments": {
    "default": {
      "php": "8.2",
      "wp": "stable"
    }
  }
}
JSON
		); // Raw output is needed for regex

		$this->assertMatchesPrecommandSnapshot( $output );
	}

	public function test_env_up_with_parameters() {
		$output = qit_precommand( [
				'env:up',
				'--wp',
				'6.5',
				'--php',
				'8.3',
				'--json',
			]
		);

		$env = json_decode( $output, true );

		// Check that WordPress Version is as expected:
		$this->assertSame( '6.5', $env['env_info']['wp'] );

		// Check that PHP Version is as expected:
		$this->assertSame( '8.3', $env['env_info']['php'] );
	}

	public function test_env_up_with_object_cache() {
		$output = qit_precommand( [
				'env:up',
				'--object_cache',
				'--json',
			]
		);

		$env = json_decode( $output, true );

		$this->assertTrue( $env['env_info']['object_cache'] );
	}

	public function test_env_up_with_file() {
		$output = qit_precommand( [ 'env:up', '--json' ], <<<'JSON'
{
  "environments": {
    "default": {
      "wp": "6.4",
      "php": "8.2"
    }
  }
}
JSON
		);

		$env = json_decode( $output, true );

		// Check that WordPress Version is as expected:
		$this->assertSame( '6.4', $env['env_info']['wp'] );

		// Check that PHP Version is as expected:
		$this->assertSame( '8.2', $env['env_info']['php'] );
	}

	public function test_env_up_with_file_and_parameters() {
		$output = qit_precommand( [ 'env:up', '--json' ], <<<'JSON'
{
  "environments": {
    "default": {
      "wp": "6.4",
      "php": "8.3"
    }
  }
}
JSON
		);

		$env = json_decode( $output, true );

		// Check that WordPress Version is as expected:
		$this->assertSame( '6.4', $env['env_info']['wp'] );

		// Check that PHP Version is as expected:
		$this->assertSame( '8.3', $env['env_info']['php'] );
	}
}
