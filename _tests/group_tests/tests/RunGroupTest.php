<?php

use QIT\SelfTests\GroupTests\Traits\SnapshotHelpers;
use Spatie\Snapshots\MatchesSnapshots;

class RunGroupTest extends \PHPUnit\Framework\TestCase {
	use SnapshotHelpers;
	use MatchesSnapshots;

	public function test_run_remote_group() {
		// Clear any existing group.
		$output = qit( [
			'group:clear',
		] );

		$tests = [
            [
                'type'        => 'security',
				'run_command' => 'run:security',
                'extension'   => 'woocommerce-amazon-s3-storage',
            ],
            [
                'type'        => 'malware',
				'run_command' => 'run:malware',
                'extension'   => 'woocommerce-amazon-s3-storage',
            ]
        ];

        foreach ( $tests as $test ) {
            $output = qit( [
                $test['run_command'],
                $test['extension'],
				'--group',
            ] );

			$this->assertStringContainsString( 'Group item successfully added', $output );
        }

		// Run Show Command.
		$output = qit( [
			'group:show',
		] );

		$normalized_output = $this->normalize_untriggered_show_output( $output );
		$this->assertMatchesSnapshot( $normalized_output );	

        // Register Group.
        $output = qit( [
            'group:register'
        ] );

		$normalized_output = $this->normalized_registered_group_output( $output );
		$this->assertMatchesSnapshot( $normalized_output );

		// Run Group.
		$output = qit( [
			'group:run'
		] );

		$normalized_output = $this->normalize_remote_group_run_output( $output );
		$this->assertMatchesSnapshot( $normalized_output );

		// Complete Group Run.
		$output = qit( [
			'group:run'
		] );

		$normalized_output = $this->normalize_complete_group_run_output( $output );
		$this->assertMatchesSnapshot( $normalized_output );
	}

	public function test_run_diplicate_tests() {
		// Clear any existing group.
		$output = qit( [
			'group:clear',
		] );

		$tests = [
            [
                'type'        => 'security',
				'run_command' => 'run:security',
                'extension'   => 'woocommerce-amazon-s3-storage',
            ]
        ];

		$output = qit( [
			$tests[0]['run_command'],
			$tests[0]['extension'],
			'--group',
		] );

		$this->assertStringContainsString( 'Group item successfully added', $output );

		$output = qit( [
			$tests[0]['run_command'],
			$tests[0]['extension'],
			'--group',
		], [], 1 );


		$this->assertMatchesSnapshot( $output );
	}

	public function test_set_group_identifier() {
		// Clear any existing group.
		$output = qit( [
			'group:clear',
		] );
		
		$tests = [
            [
                'type'        => 'security',
				'run_command' => 'run:security',
                'extension'   => 'woocommerce-amazon-s3-storage',
            ]
        ];

		$output = qit( [
			$tests[0]['run_command'],
			$tests[0]['extension'],
			'--group'
		] );

		$this->assertStringContainsString( 'Group item successfully added', $output );

		// generate a random identifier.
		$identifier = bin2hex( random_bytes( 16 ) );

		$output = qit( [
			'group:run',
			'--group-identifier',
			$identifier,
		] );

		$extracted_indentifer = null;

		if ( preg_match('/Group Identifier: ([a-zA-Z0-9-_]+)/', $output, $matches) ) {
			$extracted_indentifer = $matches[1];
		}

		$this->assertEquals( $identifier, $extracted_indentifer );
	}

	public function test_group_clear() {
		// Clear any existing group.
		$output = qit( [
			'group:clear',
		] );
		// Clear any existing group.
		$output = qit( [
			'group:clear',
		] );
		$tests = [
            [
                'type'        => 'security',
				'run_command' => 'run:security',
                'extension'   => 'woocommerce-amazon-s3-storage',
            ]
        ];	

		$output = qit( [
			$tests[0]['run_command'],
			$tests[0]['extension'],
			'--group'
		] );

		$this->assertStringContainsString( 'Group item successfully added', $output );

		$output = qit( [
			'group:clear',
		] );

		$this->assertStringContainsString( 'Group cleared', $output );

		$output = qit( [
			'group:show',
		], [], 1 );

		$this->assertStringContainsString( 'No group found', $output );
	}

	public function test_fetch_by_group_identifier() {
		// Clear any existing group.
		$output = qit( [
			'group:clear',
		] );

		$tests = [
            [
                'type'        => 'security',
				'run_command' => 'run:security',
                'extension'   => 'woocommerce-amazon-s3-storage',
            ]
        ];

		$output = qit( [
			$tests[0]['run_command'],
			$tests[0]['extension'],
			'--group',
		] );

		$this->assertStringContainsString( 'Group item successfully added', $output );

		// generate a random alphanumeric identifier.
		$identifier = bin2hex( random_bytes( 16 ) );

		$output = qit( [
			'group:run',
			'--group-identifier',
			$identifier,
		] );

		$extracted_identifier = null;

		if ( preg_match('/Group Identifier: ([a-zA-Z0-9-_]+)/', $output, $matches) ) {
			$extracted_identifier = $matches[1];
		}

		$this->assertEquals( $identifier, $extracted_identifier );

		$normalized_output = $this->normalize_complete_group_run_output( $output, true );
		$this->assertMatchesSnapshot( $normalized_output );

		$output = qit( [
			'group:fetch',
			'--group-identifier',
			$identifier,
		] );

		$normalized_output = $this->normalize_complete_group_run_output( $output, true );
		$this->assertMatchesSnapshot( $normalized_output );
	}

	public function test_local_group_run() {
		// Clear any existing group.
		$output = qit( [
			'group:clear',
		] );

		$tests = [
            [
                'type'        => 'activaiton',
				'run_command' => 'run:activation',
                'extension'   => 'woocommerce-bookings',
			],
			[
				'type'        => 'activaiton',
				'run_command' => 'run:activation',
				'extension'   => 'automatewoo',
			],
        ];

		foreach ( $tests as $test ) {
			$output = qit( [
				$test['run_command'],
				$test['extension'],
				'--group',
			] );

			$this->assertStringContainsString( 'Group item successfully added', $output );
		}

		$output = qit( [
			'group:run',
		] );

		
		// At this point, both local tests would be done and we can retrieve their results from remote.
		$output = qit( [
			'group:show',
		] );

		
		$normalized_output = $this->normalize_complete_group_run_output( $output, true );
		$this->assertMatchesSnapshot( $normalized_output );	
		
	}

	public function test_local_group_run_with_remote_and_local_tests() {
		// Clear any existing group.
		$output = qit( [
			'group:clear',
		] );
		
		$tests = [
            [
                'type'        => 'activaiton',
				'run_command' => 'run:activation',
                'extension'   => 'woocommerce-bookings',
			],
			[
				'type'        => 'activaiton',
				'run_command' => 'run:activation',
				'extension'   => 'automatewoo',
			],
			[
				'type'        => 'malware',
				'run_command' => 'run:malware',
				'extension'   => 'woocommerce-amazon-s3-storage',
			],
			[
				'type'        => 'malware',
				'run_command' => 'run:malware',
				'extension'   => 'automatewoo',
			],
        ];

		foreach ( $tests as $test ) {
			$output = qit( [
				$test['run_command'],
				$test['extension'],
				'--group',
			] );

			$this->assertStringContainsString( 'Group item successfully added', $output );
		}

		$output = qit( [
			'group:run',
		] );

		
		// At this point, both local tests would be done and we can retrieve their results from remote.
		$output = qit( [
			'group:show',
		] );

		
		$normalized_output = $this->normalize_complete_group_run_output( $output, true );
		$this->assertMatchesSnapshot( $normalized_output );	
	}
}