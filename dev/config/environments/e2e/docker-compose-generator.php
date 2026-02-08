<?php

$env = getenv();

$required_envs = [
	'QIT_ENV_ID',
	'VOLUMES',
	'PHP_VERSION',
	'QIT_DOCKER_NGINX',
	'QIT_DOCKER_REDIS',
	'NORMALIZED_ENV_DIR',
	'DOMAIN',
];

$string_booleans = [ 'yes', 'no' ];

foreach ( $required_envs as $required_env ) {
	if ( ! isset( $env[ $required_env ] ) ) {
		echo "Missing required environment variable: $required_env\n";
		die( 1 );
	}
}

// Optional envs.
$env['ENV_VARS'] = json_decode( $env['ENV_VARS'], true ) ?? [];

if ( ! is_array( $env['ENV_VARS'] ) ) {
	$env['ENV_VARS'] = [];
}

$volumes = json_decode( $env['VOLUMES'], true );

if ( ! is_array( $volumes ) ) {
	echo "VOLUMES should be a JSON array.\n";
	die( 1 );
}

$current_dir = str_replace( '\\', '/', __DIR__ );
$env_dir     = $env['NORMALIZED_ENV_DIR'];

foreach ( [ 'QIT_DOCKER_NGINX', 'QIT_DOCKER_REDIS' ] as $should_be_string_booleans ) {
	if ( ! in_array( $env[ $should_be_string_booleans ], $string_booleans, true ) ) {
		echo "$should_be_string_booleans should be a string boolean.\n";
		die( 1 );
	}

	$env[ $should_be_string_booleans ] = $env[ $should_be_string_booleans ] === 'yes';
}

if ( ! preg_match( '/^[a-z0-9]+$/i', $env['QIT_ENV_ID'] ) ) {
	echo "QIT_ENV_ID should be alphanumeric only.\n";
	die( 1 );
}

if ( ! file_exists( $env_dir ) ) {
	echo "Environment dir not found: " . $env_dir . "\n";
	die( 1 );
}

// Required envs
$qit_env_id  = $env['QIT_ENV_ID'];
$php_version = $env['PHP_VERSION'];
$with_nginx  = $env['QIT_DOCKER_NGINX'];
$with_cache  = $env['QIT_DOCKER_REDIS'];
$domain      = $env['DOMAIN'];

$volumes_yml = '';

foreach ( $volumes as $in_container => $local ) {
	if ( strpos( $in_container, ':' ) !== false ) {
		list( $in_container, $flags ) = explode( ':', $in_container, 2 );
		$flags = ':' . $flags;
	} else {
		$flags = '';
	}

	if ( ! file_exists( $local ) ) {
		if ( strpos( $local, 'qit_env_volume' ) === false ) {
			echo "Local path not found: $local\n";
			die( 1 );
		}
		echo "Named volume detected: $local\n";
		$volumes_yml .= "      - $local:$in_container$flags\n";
		continue;
	}

	try {
		is_safe_mount( $local );
	} catch ( \Exception $e ) {
		echo $e;
		die( 1 );
	}

	/*
	 * Pre-create volume directories so that permissions are mapped correctly in Docker.
	 */
	if ( stripos( $in_container, '/var/www/html/' ) !== false ) {
		// Eg: [ 'wp-content', 'mu-plugins', 'foo.txt' ]
		$parts = explode( '/', str_replace( '/var/www/html/', '', $in_container ) );

		$local_path_accumulator = $env_dir . "html";

		foreach ( $parts as $p ) {
			echo "Checking $p\n";
			if ( stripos( $p, '.' ) === false ) {
				$local_path_accumulator .= "/$p"; // Append the current part to the path accumulator.

				if ( file_exists( $local_path_accumulator ) ) {
					echo "Skipping directory that already exists $p\n";
					continue;
				}

				mkdir( $local_path_accumulator, 0755, true );

				if ( file_exists( $local_path_accumulator ) ) {
					echo "Created directory $local_path_accumulator\n";
				} else {
					echo "Failed to create directory $local_path_accumulator\n";
					die( 1 );
				}
			} else {
				echo "Skipping file $p\n";
			}
		}
	}

	// Create the volumes string, following YAML indentation.
	$volumes_yml .= "      - $local:$in_container$flags\n";
}

$volumes_yml .= "      - {$env_dir}php/etc/my.cnf.d:/etc/my.cnf.d\n";

/**
 * All mounts are safe by default, but the user can try to mount
 * an unsafe volume at runtime with parameters or config, in which case we deny.
 *
 * @param string $path The local path being mounted.
 *
 * @return void
 */
function is_safe_mount( string $path ): void {
	if ( strpos( $path, '.ssh' ) !== false ) {
		throw new \Exception( "Mounting .ssh is not allowed." );
	}
}

$volumes_yml = rtrim( $volumes_yml );

$docker_images = [
	'db'      => 'jbergstroem/mariadb-alpine:10.11.5',
	'php_fpm' => "automattic/qit-runner-php-$php_version-fpm-alpine:latest",
	'nginx'   => 'nginx:stable-alpine3.21-slim',
	'cache'   => 'redis:7.4.5-alpine',
];

$network_name = "qit_network_$qit_env_id";

if ( $domain !== 'localhost' ) {
	$extra_hosts = "      - \"$domain:host-gateway\"";
} else {
	$extra_hosts = '';
}

$env_vars_yml = '';

foreach ( $env['ENV_VARS'] as $env_key => $env_value ) {
	$env_vars_yml .= "      - $env_key=$env_value\n";
}

$current_file = __FILE__;

$header = <<<YML
##
## This file is generated from $current_file and it's meant to be used on a disposable CI environment.
##
YML;

$networks_yml = <<<YML
networks:
  $network_name:
    driver: bridge
    enable_ipv6: false
YML;

$named_volumes_yml = <<<YML
volumes:
  qit_env_volume_db_$qit_env_id:
  qit_env_volume_$qit_env_id:
YML;

/*
 * The DB Dockerfile has a VOLUME definition mapped to "/var/lib/mysql".
 * By default, Docker will create an anonymous volume for this mount
 * whenever the container is started.
 * This will cause the volume to be lying around after the disposable
 * environment is destroyed, which is not optimal.
 * To avoid this, we create a named volume for the DB data directory.
 * This named volume follows a pattern that we look for when doing
 * dangling environment cleanups.
 */
$db_named_volume = "qit_env_volume_db_$qit_env_id";

/*
 * Init service: creates the directory structure and sets correct permissions
 * in the named volume before the PHP container starts.
 * Directories are owned by 82:82 (www-data UID in Alpine PHP images).
 * Once the PHP container starts, FixUID remaps these to the runtime UID.
 */
$init_service = <<<YML
  qit_env_init_$qit_env_id:
    image: busybox
    container_name: qit_env_init_$qit_env_id
    network_mode: none
    volumes:
      - qit_env_volume_$qit_env_id:/var/www/html
    command: sh -c "mkdir -p /var/www/html/wp-content/plugins && mkdir -p /var/www/html/wp-content/themes && mkdir -p /var/www/html/wp-content/mu-plugins && chown -R 82:82 /var/www/html"
YML;

$database_service = <<<YML
  qit_env_db_$qit_env_id:
    image: {$docker_images['db']}
    container_name: qit_env_db_$qit_env_id
    command:
      --max_allowed_packet=128000000
      --innodb_log_file_size=128000000 
    environment:
      - MYSQL_DATABASE=wordpress
      - MYSQL_USER=admin
      - MYSQL_PASSWORD=password
      - MYSQL_ROOT_PASSWORD=password
    healthcheck:
      test: "mariadb --host=qit_env_db_$qit_env_id --user=root --password=password wordpress --execute 'SELECT 1'"
      interval: 2s
      retries: 10
    networks:
      - $network_name
    volumes:
      - $db_named_volume:/var/lib/mysql
YML;

$php_fpm_service = <<<YML
  qit_env_php_$qit_env_id:
    image: {$docker_images['php_fpm']} 
    container_name: qit_env_php_$qit_env_id
    user: \${FIXUID:-1000}:\${FIXGID:-1000}
    environment:
      - WP_CLI_CACHE_DIR=/qit/cache/wp-cli
$env_vars_yml
    depends_on:
      qit_env_init_$qit_env_id:
        condition: service_completed_successfully
      qit_env_db_$qit_env_id:
        condition: service_healthy
    volumes:
$volumes_yml
    extra_hosts:
      - "host.docker.internal:host-gateway"
$extra_hosts
    networks:
      - $network_name
YML;

$nginx_service = <<<YML
  qitenvnginx$qit_env_id:
    image: {$docker_images['nginx']}
    container_name: qit_env_nginx_$qit_env_id
    depends_on:
      - qit_env_php_$qit_env_id
    restart: on-failure:1
    ports:
      - "80"
    networks:
      - $network_name
    volumes:
      - $current_dir/docker/nginx/conf.d:/etc/nginx/conf.d
$volumes_yml
YML;

$cache_service = <<<YML
  qit_env_cache_$qit_env_id:
    image: {$docker_images['cache']}
    container_name: qit_env_cache_$qit_env_id
    networks:
      - $network_name
    ports:
      - '6379'
YML;

$docker_compose = <<<DOCKER_COMPOSE
$header

services:
$init_service
$database_service
$php_fpm_service
DOCKER_COMPOSE;

if ( $with_nginx ) {
	$docker_compose .= "\n$nginx_service";
}

if ( $with_cache ) {
	$docker_compose .= "\n$cache_service";
}

$docker_compose .= "\n$networks_yml";
$docker_compose .= "\n$named_volumes_yml";

// Inject /etc/my.cnf.d/disable-ssl.cnf into the PHP container build context.
$my_cnf_path = $env_dir . 'php/etc/my.cnf.d';
if ( ! is_dir( $my_cnf_path ) ) {
	mkdir( $my_cnf_path, 0755, true );
}
file_put_contents( "$my_cnf_path/disable-ssl.cnf", "[client]\nskip-ssl\n" );


file_put_contents( __DIR__ . '/docker-compose.yml', $docker_compose );
file_put_contents( __DIR__ . '/html/phpinfo.php', <<<'PHP'
<?php
echo "\nget_loaded_extensions():\n";
$loaded_extensions = get_loaded_extensions();
sort($loaded_extensions); 
echo json_encode( $loaded_extensions, JSON_PRETTY_PRINT ) . "\n";

$expected_extensions = json_decode($_GET['php_extensions']);

echo "Expecting extensions: {$_GET['php_extensions']} \n";

foreach ($expected_extensions as $ext) {
	if (!in_array($ext, $loaded_extensions)) {
		echo "\nERROR: $ext is not loaded\n";
		exit(1);
	} else {
		echo "\nOK: $ext is loaded\n";
	}
}
PHP
);// Force update 1755457482
// Test QoL 1755457862
