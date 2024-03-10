<?php

namespace QIT_CLI_Tests;

use QIT_CLI\App;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Environment\Environments\EnvInfo;
use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Component\Serializer\SerializerInterface;

class EnvInfoTest extends QITTestCase {
	use MatchesSnapshots;

	public function test_serialize_env_info() {
		$env_info                = new E2EEnvInfo();
		$env_info->type          = 'e2e';
		$env_info->temporary_env = '/tmp/test';
		$env_info->created_at    = 123;
		$env_info->status        = 'running';
		$env_info->docker_images = [ 'test1', 'test2' ];

		$serializer = App::make( SerializerInterface::class );

		$this->assertMatchesJsonSnapshot( $serializer->serialize( $env_info, 'json' ) );
	}

	public function test_unserialize_env_info() {
		$json = '{"type":"e2e","temporary_env":"\/tmp\/test","created_at":123,"status":"running","docker_images":["test1","test2"]}';

		$serializer = App::make( SerializerInterface::class );

		/** @var E2EEnvInfo $env_info */
		$env_info = $serializer->deserialize( $json, EnvInfo::class, 'json' );

		$this->assertEquals( 'e2e', $env_info->type );
		$this->assertEquals( '/tmp/test', $env_info->temporary_env );
		$this->assertEquals( 123, $env_info->created_at );
		$this->assertEquals( 'running', $env_info->status );
		$this->assertEquals( [ 'test1', 'test2' ], $env_info->docker_images );
	}

	public function test_unserialize_env_info_yml() {
		$yml = <<<YML
type: e2e
temporary_env: /tmp/test
created_at: 123
status: running
docker_images:
  - test1
  - test2
YML;

		$serializer = App::make( SerializerInterface::class );

		/** @var E2EEnvInfo $env_info */
		$env_info = $serializer->deserialize( $yml, EnvInfo::class, 'yml' );

		$this->assertEquals( 'e2e', $env_info->type );
		$this->assertEquals( '/tmp/test', $env_info->temporary_env );
		$this->assertEquals( 123, $env_info->created_at );
		$this->assertEquals( 'running', $env_info->status );
		$this->assertEquals( [ 'test1', 'test2' ], $env_info->docker_images );
	}
}