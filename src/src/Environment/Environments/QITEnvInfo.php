<?php

namespace QIT_CLI\Environment\Environments;

/**
 * Base class for QIT test environment info (E2E, Performance, etc.)
 * Contains common properties shared across different test environment types.
 */
abstract class QITEnvInfo extends EnvInfo {
	/**
	 * Infrastructure provider type.
	 *
	 * @var string 'local' (Docker) or 'cloud'
	 */
	public string $provider_type = 'local';

	/**
	 * Cloud environment ID for cloud provider.
	 * Used to identify the environment in Manager API calls.
	 *
	 * @var string|null
	 */
	public ?string $cloud_environment_id = null;

	/** @var string WordPress version */
	public string $wordpress_version = '';

	/** @var string WooCommerce version */
	public string $woocommerce_version = '';

	/** @var string PHP version */
	public string $php_version = '';

	/** @var bool Whether to enable Redis object cache */
	public bool $object_cache = false;

	/** @var bool Whether to restrict network access */
	public bool $network_restriction = true;

	/** @var string Nginx port */
	public string $nginx_port = '';

	/** @var string PHP container name */
	public string $php_container = '';

	/** @var string Database container name */
	public string $db_container = '';

	/** @var bool Whether to skip test package phases */
	public bool $skip_test_phases = false;

	/** @var bool Whether to skip activating plugins */
	public bool $skip_activating_plugins = false;

	/** @var bool Whether to skip activating themes */
	public bool $skip_activating_themes = false;

	/** @var array<string,string> Additional environment variables */
	public array $additional_vars = [];

	/** @var array<string,array<string,array<mixed>>> Test packages configuration */
	public array $test_packages = [];

	/** @var array<string,array{path:string,source:string,container_path:string,package_id?:string,manifest?:array<string,mixed>}> Test packages for setup phase */
	public array $test_packages_for_setup = [];

	/** @var array<string,mixed> System under test information (slug, type, entrypoint, path, id) */
	public array $sut = [];
}
