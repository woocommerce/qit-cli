<?php

namespace QIT_CLI\PreCommand\Download\Extensions;

use QIT_CLI\App;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Environment\Extension;
use QIT_CLI\Zipper;
use Symfony\Component\Console\Output\OutputInterface;

class ExtensionDownloader {
	/** @var OutputInterface $output */
	protected $output;

	/** @var Zipper $extension_zip */
	protected $extension_zip;

	public function __construct(
		OutputInterface $output,
		Zipper $extension_zip
	) {
		$this->output        = $output;
		$this->extension_zip = $extension_zip;
	}

	/**
	 * Downloads and extracts extensions, setting up volume mappings.
	 *
	 * @param EnvInfo $env_info
	 * @param string $cache_dir
	 * @param array<Extension> $plugins
	 * @param array<Extension> $themes
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

		$find_entrypoint = function ( Extension $e, string $base_dir ) {
			if ( $e->type === Extension::TYPES['theme'] ) {
				if ( ! file_exists( "$base_dir/style.css" ) ) {
					throw new \RuntimeException( "The extracted zip '{$e->downloaded_source}' file does not contain a style.css file." );
				}
				$e->entrypoint = "{$e->slug}/style.css";
			} elseif ( $e->type === Extension::TYPES['plugin'] ) {
				if ( file_exists( "$base_dir/{$e->slug}.php" ) ) {
					$e->entrypoint = "{$e->slug}/{$e->slug}.php";
				} else {
					foreach ( new \DirectoryIterator( $base_dir ) as $file ) {
						if ( $file->isFile() && $file->getExtension() === 'php' ) {
							$contents = file_get_contents( $file->getPathname() );
							if ( preg_match( '#Plugin Name:#', $contents ) ) {
								$e->entrypoint = "{$e->slug}/{$file->getFilename()}";
								break;
							}
						}
					}
				}
			}
		};

		foreach ( $extensions as $e ) {
			if ( ! file_exists( $e->downloaded_source ) ) {
				throw new \RuntimeException( 'Download failed for ' . $e->slug );
			}

			clearstatcache( true, $e->downloaded_source );

			if ( is_file( $e->downloaded_source ) ) {
				$this->extension_zip->extract_zip( $e->downloaded_source, "$env_info->temporary_env/html/wp-content/{$e->type}s" );
				if ( ! file_exists( "$env_info->temporary_env/html/wp-content/{$e->type}s/{$e->slug}" ) ) {
					throw new \RuntimeException( "The extracted zip '{$e->downloaded_source}' file does not contain a parent directory matching the slug '{$e->slug}'." );
				}
				$find_entrypoint( $e, "$env_info->temporary_env/html/wp-content/{$e->type}s/{$e->slug}" );
				if ( ! isset( $e->entrypoint ) ) {
					throw new \RuntimeException( "We could not find a valid entrypoint for the zip extracted at '{$e->downloaded_source}'." );
				}
				if ( getenv( 'QIT_SUT' ) === $e->slug && $env_info instanceof E2EEnvInfo ) {
					$env_info->sut_entrypoint = $e->entrypoint;
					$env_info->sut_slug       = $e->slug;
					$env_info->sut_path       = "$env_info->temporary_env/html/wp-content/{$e->type}s/{$e->slug}";
				}
				$env_info->volumes["/var/www/html/wp-content/{$e->type}s/{$e->slug}"] = "$env_info->temporary_env/html/wp-content/{$e->type}s/{$e->slug}";
			} elseif ( is_dir( $e->downloaded_source ) ) {
				if ( ! getenv( 'QIT_ALLOW_WRITE' ) ) {
					$this->output->writeln( "Info: Mapping '{$e->type}s/{$e->slug}' as read-only to protect your local copy." );
					$env_info->volumes["/var/www/html/wp-content/{$e->type}s/{$e->slug}:ro,cached"] = $e->downloaded_source;
				} else {
					$env_info->volumes["/var/www/html/wp-content/{$e->type}s/{$e->slug}"] = $e->downloaded_source;
				}
				$find_entrypoint( $e, $e->downloaded_source );
				if ( ! isset( $e->entrypoint ) ) {
					throw new \RuntimeException( "We could not find a valid entrypoint for the directory '{$e->downloaded_source}'." );
				}
				if ( getenv( 'QIT_SUT' ) === $e->slug && $env_info instanceof E2EEnvInfo ) {
					$env_info->sut_entrypoint = $e->entrypoint;
					$env_info->sut_slug       = $e->slug;
					$env_info->sut_path       = $e->downloaded_source;
				}
			} else {
				throw new \RuntimeException( 'Download failed for ' . $e->slug );
			}
		}
	}

	/**
	 * Categorizes extensions by their 'from' property and assigns handlers.
	 *
	 * @param array<Extension> $plugins
	 * @param array<Extension> $themes
	 *
	 * @return array<Extension>
	 */
	public function categorize_extensions( array $plugins, array $themes ): array {
		$categorized_extensions = [];

		foreach ( [ 'plugin' => $plugins, 'theme' => $themes ] as $type => $extensions ) {
			foreach ( $extensions as $ext ) {
				if ( ! $ext instanceof Extension ) {
					throw new \LogicException( 'Invalid extension object.' );
				}
				if ( empty( $ext->from ) ) {
					throw new \LogicException( 'Extension "from" property is required for ' . $ext->slug );
				}
				if ( empty( $ext->slug ) ) {
					throw new \LogicException( 'Extension slug is required.' );
				}
				if ( array_key_exists( $ext->slug, $categorized_extensions ) ) {
					throw new \InvalidArgumentException( 'Duplicate extension found: ' . $ext->slug );
				}

				// Check for custom handlers
				foreach ( get_declared_classes() as $class ) {
					if ( is_subclass_of( $class, CustomHandler::class ) ) {
						$handler = App::make( $class );
						if ( $handler->should_handle( $ext ) ) {
							$this->output->writeln( "Custom handler '$class' is handling '{$ext->slug}'." );
							$ext->handler                         = $class;
							$categorized_extensions[ $ext->slug ] = $ext;
							continue 2;
						}
					}
				}

				// Assign handler based on 'from' property
				switch ( $ext->from ) {
					case 'wporg':
						$ext->handler = WPOrgDownloadHandler::class;
						break;
					case 'wccom':
						$ext->handler = WCCOMDownloadHandler::class;
						break;
					case 'zip':
						$ext->handler = ZipDownloadHandler::class;
						break;
					case 'local':
						$ext->handler = LocalDownloadHandler::class;
						break;
					default:
						throw new \InvalidArgumentException( "Invalid 'from' value '{$ext->from}' for extension '{$ext->slug}'." );
				}

				$categorized_extensions[ $ext->slug ] = $ext;
			}
		}

		return $categorized_extensions;
	}

	/**
	 * Validate if the given string is a valid slug.
	 */
	public static function is_valid_plugin_slug( string $slug ): bool {
		return preg_match( '/^[a-z0-9_]+([-\.][a-z0-9_]+)*$/', $slug );
	}
}