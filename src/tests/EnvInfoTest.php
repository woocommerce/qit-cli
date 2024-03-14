<?php

namespace QIT_CLI_Tests;

use QIT_CLI\Environment\Environments\EnvInfo;
use Spatie\Snapshots\MatchesSnapshots;

class EnvInfoTests extends QITTestCase {
	use MatchesSnapshots;

	public function test_serialize_env_info() {
		$array                   = [ 'environment' => 'e2e' ];
		$env_info                = EnvInfo::from_array( $array );
		$env_info->temporary_env = '/tmp/test';
		$env_info->created_at    = 123;
		$env_info->status        = 'running';
		$env_info->docker_images = [ 'test1', 'test2' ];
		$env_info->env_id        = 'Normalized';

		$this->assertMatchesJsonSnapshot( json_encode( $env_info ) );
	}

	public function test_unserialize_env_info() {
		$env_info = json_decode( '{"environment":"e2e","temporary_env":"\/tmp\/test","created_at":123,"status":"running","docker_images":["test1","test2"]}', true );

		$this->assertEquals( 'e2e', $env_info['environment'] );
		$this->assertEquals( '/tmp/test', $env_info['temporary_env'] );
		$this->assertEquals( 123, $env_info['created_at'] );
		$this->assertEquals( 'running', $env_info['status'] );
		$this->assertEquals( [ 'test1', 'test2' ], $env_info['docker_images'] );
	}
}