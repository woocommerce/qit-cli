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

	public function test_provider_type_defaults_to_local() {
		$array    = [ 'environment' => 'e2e' ];
		$env_info = EnvInfo::from_array( $array );

		$this->assertEquals( 'local', $env_info->provider_type );
	}

	public function test_cloud_environment_id_defaults_to_null() {
		$array    = [ 'environment' => 'e2e' ];
		$env_info = EnvInfo::from_array( $array );

		$this->assertNull( $env_info->cloud_environment_id );
	}

	public function test_provider_type_is_deserialized_from_array() {
		$array = [
			'environment'   => 'e2e',
			'provider_type' => 'cloud',
		];
		$env_info = EnvInfo::from_array( $array );

		$this->assertEquals( 'cloud', $env_info->provider_type );
	}

	public function test_cloud_environment_id_is_deserialized_from_array() {
		$array = [
			'environment'           => 'e2e',
			'cloud_environment_id'  => 'cloud-env-abc123',
		];
		$env_info = EnvInfo::from_array( $array );

		$this->assertEquals( 'cloud-env-abc123', $env_info->cloud_environment_id );
	}

	public function test_provider_type_is_serialized_to_json() {
		$array                   = [ 'environment' => 'e2e' ];
		$env_info                = EnvInfo::from_array( $array );
		$env_info->provider_type = 'cloud';

		$serialized = json_decode( json_encode( $env_info ), true );

		$this->assertArrayHasKey( 'provider_type', $serialized );
		$this->assertEquals( 'cloud', $serialized['provider_type'] );
	}

	public function test_cloud_environment_id_is_serialized_to_json() {
		$array                          = [ 'environment' => 'e2e' ];
		$env_info                       = EnvInfo::from_array( $array );
		$env_info->cloud_environment_id = 'cloud-env-xyz789';

		$serialized = json_decode( json_encode( $env_info ), true );

		$this->assertArrayHasKey( 'cloud_environment_id', $serialized );
		$this->assertEquals( 'cloud-env-xyz789', $serialized['cloud_environment_id'] );
	}

	public function test_backward_compatibility_without_provider_fields() {
		// Simulate loading old cached data without provider fields
		$old_cached_data = [
			'environment'    => 'e2e',
			'temporary_env'  => '/tmp/old-env',
			'created_at'     => 12345,
			'status'         => 'running',
			'docker_images'  => [ 'image1' ],
		];
		$env_info = EnvInfo::from_array( $old_cached_data );

		// Should use default values
		$this->assertEquals( 'local', $env_info->provider_type );
		$this->assertNull( $env_info->cloud_environment_id );

		// Existing fields should still work
		$this->assertEquals( '/tmp/old-env', $env_info->temporary_env );
		$this->assertEquals( 12345, $env_info->created_at );
	}

	public function test_provider_type_roundtrip() {
		$original = [
			'environment'          => 'e2e',
			'provider_type'        => 'cloud',
			'cloud_environment_id' => 'test-cloud-id',
		];
		$env_info   = EnvInfo::from_array( $original );
		$serialized = json_decode( json_encode( $env_info ), true );
		$restored   = EnvInfo::from_array( $serialized );

		$this->assertEquals( 'cloud', $restored->provider_type );
		$this->assertEquals( 'test-cloud-id', $restored->cloud_environment_id );
	}
}