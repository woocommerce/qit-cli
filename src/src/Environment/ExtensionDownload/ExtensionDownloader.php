<?php

namespace QIT_CLI\Environment\ExtensionDownload;

use QIT_CLI\App;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Environment\Extension;
use QIT_CLI\Environment\ExtensionDownload\Handlers\CustomHandler;
use QIT_CLI\Environment\ExtensionDownload\Handlers\FileHandler;
use QIT_CLI\Environment\ExtensionDownload\Handlers\QITHandler;
use QIT_CLI\Environment\ExtensionDownload\Handlers\URLHandler;
use QIT_CLI\WooExtensionsList;
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

		$find_entrypoint = function ( Extension $e, string $base_dir ) {
			// Set the entrypoint of the extension.
			if ( $e->type === Extension::TYPES['theme'] ) {
				if ( ! file_exists( "$base_dir/style.css" ) ) {
					throw new \RuntimeException( "The extracted zip '{$e->downloaded_source}' file does not contain a style.css file." );
				}

				$e->entrypoint = "{$e->slug}/style.css";
			} elseif ( $e->type === Extension::TYPES['plugin'] ) {
				// Give precedence to the main PHP file as we expect to find it: Matching the parent directory.
				if ( file_exists( "$base_dir/{$e->slug}.php" ) ) {
					$e->entrypoint = "{$e->slug}/{$e->slug}.php";
				} else {
					// If that does not exist, find the first PHP file in that directory with a Plugin Name.
					foreach ( new \DirectoryIterator( $base_dir ) as $file ) {
						if ( $file->isFile() && $file->getExtension() === 'php' ) {
							$contents = file_get_contents( $file->getPathname() );
							if ( preg_match( '#Plugin Name:#', $contents, $matches ) ) {
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
				throw new \RuntimeException( 'Download failed.' );
			}

			clearstatcache( true, $e->downloaded_source );

			if ( is_file( $e->downloaded_source ) ) {
				// Extract zip to temp environment.
				$this->extension_zip->extract_zip( $e->downloaded_source, "$env_info->temporary_env/html/wp-content/{$e->type}s" );

				if ( ! file_exists( "$env_info->temporary_env/html/wp-content/{$e->type}s/{$e->slug}" ) ) {
					/*
					 * We extracted the zip, and we couldn't find a directory matching the slug, which
					 * probably means the zip file has a parent directory that does not match the slug.
					 * Inform to user and bail.
					 */
					throw new \RuntimeException( "The extracted zip '{$e->downloaded_source}' file does not contain a parent directory matching the slug '{$e->slug}'." );
				}

				$find_entrypoint( $e, "$env_info->temporary_env/html/wp-content/{$e->type}s/{$e->slug}" );

				// @phpstan-ignore-next-line
				if ( ! isset( $e->entrypoint ) ) {
					throw new \RuntimeException( "We could not find a valid entrypoint for the zip extracted at '{$e->downloaded_source}'." );
				}

				if ( getenv( 'QIT_SUT' ) === $e->slug && $env_info instanceof E2EEnvInfo ) {
					$env_info->sut_entrypoint = $e->entrypoint;
					$env_info->sut_path       = "/var/www/html/wp-content/{$e->type}s/{$e->slug}/{$e->entrypoint}";
				}

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

				$find_entrypoint( $e, $e->downloaded_source );

				// @phpstan-ignore-next-line
				if ( ! isset( $e->entrypoint ) ) {
					throw new \RuntimeException( "We could not find a valid entrypoint for the directory '{$e->downloaded_source}'." );
				}

				if ( getenv( 'QIT_SUT' ) === $e->slug && $env_info instanceof E2EEnvInfo ) {
					$env_info->sut_entrypoint = $e->entrypoint;
					$env_info->sut_path       = $e->downloaded_source;
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
				if ( ! $ext instanceof Extension ) {
					throw new \LogicException( 'Invalid extension object.' );
				}

				// At this point, source for all extensions should be set.
				if ( empty( $ext->source ) ) {
					throw new \LogicException( 'Extension source is required.' );
				}

				// At this point, slug should already be inferred from the source.
				if ( empty( $ext->slug ) ) {
					throw new \LogicException( 'Extension slug should be defined at this point.' );
				}

				// @phan-suppress-next-line PhanPossiblyUndeclaredVariable
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

				try {
					$qit_extension = App::make( WooExtensionsList::class )->get_woo_extension_id_by_slug( $ext->source );
				} catch ( \Exception $e ) {
					$qit_extension = null;
				}

				if ( empty( $ext->handler ) ) {
					if ( ! is_null( $qit_extension ) ) {
						// A QIT extension slug that this user has access to.
						$ext->handler = QITHandler::class;
					} elseif ( is_numeric( $ext->source ) ) {
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
					} elseif ( static::is_valid_plugin_slug( $ext->source ) ) {
						// If it looks like a slug, let QITHandler handle it, this includes WPOrg slugs.
						$ext->handler = QITHandler::class;
					} elseif ( file_exists( $ext->source ) ) {
						// Local path.
						$ext->handler = FileHandler::class;
					} else {
						$error_message = 'Could not find extension ' . $ext->source;

						$d = $ext->source;

						// If it's inside the filesystem, tell that the file was not found.
						while ( true ) {
							$d = realpath( dirname( $d ) );
							if ( file_exists( $d ) ) {
								$error_message .= sprintf( "\nFile \"%s\" was not found.", $ext->source );
								break;
							}
						}

						throw new \InvalidArgumentException( $error_message );
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
