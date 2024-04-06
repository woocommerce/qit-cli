<?php

namespace QIT_CLI\Environment\ExtensionDownload;

use QIT_CLI\App;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Environment\Extension;
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
	 * @param EnvInfo          $env_info
	 * @param string           $cache_dir
	 * @param array<Extension> $plugins Accepts paths, Woo.com slugs/product IDs, WordPress.org slugs or GitHub URLs.
	 * @param array<Extension> $themes Accepts paths, Woo.com slugs/product IDs, WordPress.org slugs or GitHub URLs.
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

		foreach ( $extensions as $e ) {
			if ( ! file_exists( $e->downloaded_source ) ) {
				throw new \RuntimeException( 'Download failed.' );
			}

			clearstatcache( true, $e->downloaded_source );

			if ( is_file( $e->downloaded_source ) ) {
				// Extract zip to temp environment.
				$this->extension_zip->extract_zip( $e->downloaded_source, "$env_info->temporary_env/html/wp-content/{$e->type}s" );
				// Add a volume bind.
				$env_info->volumes[ "/var/www/html/wp-content/{$e->type}s/{$e->slug}" ] = "$env_info->temporary_env/html/wp-content/{$e->type}s/{$e->slug}";
			} elseif ( is_dir( $e->downloaded_source ) ) {
				if ( ! getenv( 'QIT_ALLOW_WRITE' ) ) {
					// Set it as read-only to prevent dev messing up their local copy inadvertently (default behavior).

					// Inform the user about the read-only mapping.
					$this->output->writeln( "Info: Mapping '{$e->type}s/{$e->slug}' as read-only to protect your local copy." );

					// Add a read-only volume bind.
					$env_info->volumes[ "/var/www/html/wp-content/{$e->type}s/{$e->slug}:ro,cached" ] = $e->downloaded_source;
				} else {
					// Add a volume bind.
					$env_info->volumes[ "/var/www/html/wp-content/{$e->type}s/{$e->slug}" ] = $e->downloaded_source;
				}
			} else {
				throw new \RuntimeException( 'Download failed.' );
			}
		}
	}

	/**
	 * @param array<Extension> $plugins
	 * @param array<Extension> $themes
	 *
	 * @return array<Extension>
	 */
	public function categorize_extensions( array $plugins, array $themes ): array {
		/**
		 * @param array<int, Extension> $categorized_extensions
		 */
		$categorized_extensions = [];

		foreach (
			[
				'plugin' => $plugins,
				'theme'  => $themes,
			] as $type => $extensions
		) {
			foreach ( $extensions as $ext ) {
				if ( array_key_exists( $ext->slug, $categorized_extensions ) ) {
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
							$this->output->writeln( "Custom handler '$class' is handling '{$ext->slug}'." );
							$ext->handler = $class;
						}
					}
				}

				if ( empty( $ext->handler ) ) {
					if ( is_numeric( $ext->source ) ) {
						// Woo.com product ID.
						$ext->handler = QITHandler::class;
					} elseif ( preg_match( '#^https?://#i', $ext->source ) ) {
						$ext->handler = URLHandler::class;
					} elseif ( preg_match( '#^[\w-]+/[\w-]+(?:\#[\w-]+)?$#', $ext->source ) ) {
						// GitHub Repo, similar to wp-env.
						throw new \InvalidArgumentException( 'Installing from GitHub repositories is not supported yet.' );
					} elseif ( preg_match( '#^ssh://#i', $ext->source ) ) {
						// SSH URLs, similar to wp-env.
						throw new \InvalidArgumentException( 'SSH URLs are currently not supported.' );
					} elseif ( file_exists( $ext->source ) ) {
						// Local path.
						$ext->handler = FileHandler::class;
					} else {
						// If it's none of the above, it's a slug.
						if ( static::is_valid_plugin_slug( $ext->source ) ) {
							$ext->handler = QITHandler::class;
						} else {
							throw new \InvalidArgumentException( 'The provided string could not be parsed as any of the valid formats: WP.org/Woo.com Slugs, Woo.com product ID, Local path, or Zip URLs.' );
						}
					}
				}

				$categorized_extensions[ $ext->slug ] = $ext;
			}
		}

		return $categorized_extensions;
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
