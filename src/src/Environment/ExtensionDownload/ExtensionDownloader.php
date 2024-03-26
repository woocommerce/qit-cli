<?php

namespace QIT_CLI\Environment\ExtensionDownload;

use QIT_CLI\App;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Environment\ExtensionDownload\Handlers\CustomHandler;
use QIT_CLI\Environment\ExtensionDownload\Handlers\FileHandler;
use QIT_CLI\Environment\ExtensionDownload\Handlers\QITHandler;
use QIT_CLI\Environment\ExtensionDownload\Handlers\URLHandler;
use QIT_CLI\Zipper;
use Symfony\Component\Console\Output\OutputInterface;

class ExtensionDownloader {
	/** @var OutputInterface $output */
	protected $output;

	/** @var Zipper $extension_zip */
	protected $extension_zip;

	/** @var QITHandler $qit_handler */
	protected $qit_handler;

	/** @var URLHandler $url_handler */
	protected $url_handler;

	/** @var FileHandler $file_handler */
	protected $file_handler;

	public function __construct(
		OutputInterface $output,
		Zipper $extension_zip,
		QITHandler $qit_handler,
		URLHandler $url_handler,
		FileHandler $file_handler
	) {
		$this->output        = $output;
		$this->extension_zip = $extension_zip;
		$this->qit_handler   = $qit_handler;
		$this->url_handler   = $url_handler;
		$this->file_handler  = $file_handler;
	}

	/**
	 * @param EnvInfo           $env_info
	 * @param string            $cache_dir
	 * @param array<string|int> $plugins Accepts paths, Woo.com slugs/product IDs, WordPress.org slugs or GitHub URLs.
	 * @param array<string|int> $themes Accepts paths, Woo.com slugs/product IDs, WordPress.org slugs or GitHub URLs.
	 *
	 * @return void
	 */
	public function download( EnvInfo $env_info, string $cache_dir, array $plugins = [], array $themes = [] ): void {
		$extensions = $this->categorize_extensions( $plugins, $themes );

		$handlers_to_use = [];

		foreach ( $extensions as $e ) {
			if ( ! array_key_exists( $e->handler, $handlers_to_use ) ) {
				$handlers_to_use[ $e->handler ] = [];
			}
			$handlers_to_use[ $e->handler ][] = $e;
		}

		foreach ( $handlers_to_use as $handler_type => $e ) {
			App::make( $handler_type )->populate_extension_versions( $e );
			App::make( $handler_type )->maybe_download_extensions( $e, $cache_dir );
		}

		$is_docker = file_exists( '/.dockerenv' );

		foreach ( $extensions as $e ) {
			if ( ! file_exists( $e->path ) ) {
				throw new \RuntimeException( 'Download failed.' );
			}

			clearstatcache( true, $e->path );

			if ( is_file( $e->path ) ) {
				// Extract zip to temp environment.
				if ($is_docker) {
					$this->extension_zip->extract_zip( $e->path, "/var/www/html/wp-content/{$e->type}s" );
				} else {
					$this->extension_zip->extract_zip( $e->path, "$env_info->temporary_env/html/wp-content/{$e->type}s" );
					// Add a volume bind.
					$env_info->volumes[ "/var/www/html/wp-content/{$e->type}s/{$e->extension_identifier}" ] = "$env_info->temporary_env/html/wp-content/{$e->type}s/{$e->extension_identifier}";
				}
			} elseif ( is_dir( $e->path ) ) {
				if ( ! getenv( 'QIT_ALLOW_WRITE' ) ) {
					// Set it as read-only to prevent dev messing up their local copy inadvertently (default behavior).

					// Inform the user about the read-only mapping.
					$this->output->writeln( "Notice: Mapping '{$e->type}s/{$e->extension_identifier}' as read-only to protect your local copy." );

					// Add a read-only volume bind.
					$env_info->volumes[ "/var/www/html/wp-content/{$e->type}s/{$e->extension_identifier}:ro" ] = $e->path;
				} else {
					// Add a volume bind.
					$env_info->volumes[ "/var/www/html/wp-content/{$e->type}s/{$e->extension_identifier}" ] = $e->path;
				}
			} else {
				throw new \RuntimeException( 'Download failed.' );
			}
		}
	}

	/**
	 * @param array<string|int> $plugins
	 * @param array<string|int> $themes
	 *
	 * @return array<Extension>
	 */
	public function categorize_extensions( array $plugins, array $themes ): array {
		/**
		 * @param array<int, Extension> $extensions
		 */
		$extensions = [];

		foreach ( [
			'plugin' => $plugins,
			'theme'  => $themes,
		] as $type => $extension_ids ) {
			foreach ( $extension_ids as $extension_id ) {
				$ext                       = new Extension();
				$ext->extension_identifier = $extension_id;
				$ext->type                 = $type;
				$ext->path                 = '';

				if ( array_key_exists( $extension_id, $extensions ) ) {
					throw new \InvalidArgumentException( 'Duplicate extension found.' );
				}

				/*
				 * If there is any custom handler, allow it to handle the extension.
				 * This allows devs to implement custom handlers to fetch extensions
				 * from premium marketplaces that QIT doesn't support.
				 */
				foreach ( get_declared_classes() as $class ) {
					if ( is_subclass_of( $class, CustomHandler::class ) ) {
						$handler = App::make( $class );
						if ( $handler->should_handle( $ext ) ) {
							$this->output->writeln( "Custom handler '$class' is handling '{$ext->extension_identifier}'." );
							$ext->handler = $class;
						}
					}
				}

				if ( empty( $ext->handler ) ) {
					if ( is_numeric( $extension_id ) ) {
						// Woo.com product ID.
						$ext->handler = QITHandler::class;
					} elseif ( preg_match( '#^https?://#i', $extension_id ) ) {
						$ext->handler = URLHandler::class;
					} elseif ( preg_match( '#^[\w-]+/[\w-]+(?:\#[\w-]+)?$#', $extension_id ) ) {
						// GitHub Repo, similar to wp-env.
						throw new \InvalidArgumentException( 'Installing from GitHub repositories is not supported yet.' );
					} elseif ( preg_match( '#^ssh://#i', $extension_id ) ) {
						// SSH URLs, similar to wp-env.
						throw new \InvalidArgumentException( 'SSH URLs are currently not supported.' );
					} elseif ( file_exists( $extension_id ) ) {
						// Local path.
						$ext->handler = FileHandler::class;
					} else {
						// If it's none of the above, it's a slug.
						if ( static::is_valid_plugin_slug( $extension_id ) ) {
							$ext->handler = QITHandler::class;
						} else {
							throw new \InvalidArgumentException( 'The provided string could not be parsed as any of the valid formats: WP.org/Woo.com Slugs, Woo.com product ID, Local path, or Zip URLs.' );
						}
					}
				}

				// Call this callback so that handlers can set up some extension properties early on if needed.
				App::make( $ext->handler )->assign_handler_to_extension( $extension_id, $ext );

				$extensions[ $extension_id ] = $ext;
			}
		}

		return $extensions;
	}

	/**
	 * Validate if the given string is a valid slug.
	 *
	 * This regular expression '/^[a-z0-9]+([-\.][a-z0-9]+)*$/' breaks down as follows:
	 *
	 * ^: Asserts the start of the string.
	 * [a-z0-9]+: Matches one or more lowercase letters or numbers (the beginning of the slug).
	 * ([-\.][a-z0-9]+)*: Matches zero or more groups of a hyphen or dot followed by one or more lowercase letters or numbers. This allows for hyphens and dots within the slug but not at the start or end, nor consecutively.
	 * $: Asserts the end of the string.
	 *
	 * This pattern ensures that the slug adheres to a typical format, including the possible inclusion of dots, which are often used in version numbers or file names.
	 */
	public static function is_valid_plugin_slug( string $slug ): bool {
		return preg_match( '/^[a-z0-9_]+([-\.][a-z0-9_]+)*$/', $slug );
	}
}
