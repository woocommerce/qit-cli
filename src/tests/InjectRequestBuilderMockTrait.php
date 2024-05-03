<?php

namespace QIT_CLI_Tests;

use QIT_CLI\App;
use QIT_CLI\RequestBuilder;

trait InjectRequestBuilderMockTrait {
	public function inject_request_builder_mock() {
		$mock = new class() extends RequestBuilder {
			public function request(): string {
				global $_test_post_bodies;
				$_test_post_bodies[] = $this->post_body;

				return json_encode( [ 'upload_id' => 123 ] );
			}
		};

		App::singleton( RequestBuilder::class, $mock );
	}
}
