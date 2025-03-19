<?php

namespace QIT_CLI_Tests\Helpers;

trait EnvInfoNormalizer {
	protected function normalize_env_info( array $env_info ): array {
		$original_env_id = $env_info['env_id'];

		$normalized_env_id         = '123456';
		$env_info['env_id']        = $normalized_env_id;
		$env_info['temporary_env'] = str_replace( $original_env_id, $normalized_env_id, $env_info['temporary_env'] );
		$env_info['domain']        = str_replace( $original_env_id, $normalized_env_id, $env_info['domain'] );
		$env_info['created_at']    = 1711651749;

		return $env_info;
	}
}