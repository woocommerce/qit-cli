<?php

namespace QIT_CLI_Tests;

use QIT_CLI\App;
use QIT_CLI\PreCommand\Extensions\ExtensionMetadataFetcher;
use QIT_CLI\PreCommand\Objects\Extension;
use function QIT_CLI\get_manager_url;

class ExtensionMetadataFetcherTest extends QITTestCase {
	public function test_wccom_metadata_forwards_requested_versions_and_artifact_refs(): void {
		$slug         = 'stripe-versioned-metadata';
		$artifact_ref = [
			'source' => 'all_plugins',
			'sha'    => '473369710ab97bd1ce4286a8167633c50e37aadb',
			'url'    => 'https://example.com/artifacts/stripe-versioned-metadata-10.5.3.zip',
		];

		$url = get_manager_url() . '/wp-json/cd/v1/cli/download-urls';
		App::setVar( 'mock_' . $url, json_encode( [
			'urls' => [
				$slug => [
					'slug'              => $slug,
					'version'           => '10.5.3',
					'resolved_version'  => '10.5.3',
					'requested_version' => '10.5.x',
					'url'               => $artifact_ref['url'],
					'artifact_ref'      => $artifact_ref,
				],
			],
		] ) );

		$extension                    = new Extension( $slug, 'plugin' );
		$extension->from              = 'wccom';
		$extension->version           = '10.5.x';
		$extension->requested_version = '10.5.x';
		$extension->wccom_id          = 12345;

		App::make( ExtensionMetadataFetcher::class )->fetch_metadata( [ $extension ] );

		$request_body = App::getVar( 'mocked_request' )['post_body'];
		$this->assertSame( [ $slug => '10.5.x' ], $request_body['versions'] );
		$this->assertSame( '10.5.x', $request_body['extension_specs'][0]['requested_version'] );
		$this->assertSame( $slug, $request_body['extension_specs'][0]['slug'] );
		$this->assertSame( 12345, $request_body['extension_specs'][0]['woo_product_id'] );

		$this->assertSame( '10.5.x', $extension->requested_version );
		$this->assertSame( '10.5.3', $extension->version );
		$this->assertSame( $artifact_ref['url'], $extension->source );
		$this->assertSame( $artifact_ref, $extension->artifact_ref );
	}
}
