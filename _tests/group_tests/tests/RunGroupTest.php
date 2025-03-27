<?php

use QIT\SelfTests\GroupTests\Traits\SnapshotHelpers;
use Spatie\Snapshots\MatchesSnapshots;

class RunGroupTest extends \PHPUnit\Framework\TestCase {
	use SnapshotHelpers;
	use MatchesSnapshots;

	public function test_run_remote_group() {
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
}