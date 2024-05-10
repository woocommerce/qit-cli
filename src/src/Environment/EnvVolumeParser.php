<?php

namespace QIT_CLI\Environment;

use QIT_CLI\App;
use QIT_CLI\Config;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\normalize_path;

class EnvVolumeParser {
	/** @var OutputInterface */
	protected $output;

	public function __construct( OutputInterface $output ) {
		$this->output = $output;
	}

	/**
	 * @param array<string,string> $volumes
	 *
	 * @return array<string,string>
	 */
	public function parse_volumes( array $volumes ): array {
		try {
			$working_dir_type = App::make( WorkingDirectoryAwareness::class )->detect_working_directory_type();
		} catch ( \Exception $e ) {
			App::make( OutputInterface::class )->writeln( '<comment>Failed to detect working directory type: ' . $e->getMessage() . '</comment>' );
			$working_dir_type = null;
		}

		$mapped_automatically = null;

		if ( $working_dir_type === 'plugin' ) {
			$this->output->writeln( sprintf( '<info>Detected working directory as plugin "%s" and added a volume automatically.</info>', basename( getcwd() ) ) );
			$volumes[]            = sprintf( '%s:/var/www/html/wp-content/plugins/%s', getcwd(), basename( getcwd() ) );
			$mapped_automatically = 'plugin';
		} elseif ( $working_dir_type === 'theme' ) {
			$this->output->writeln( sprintf( '<info>Detected working directory as theme "%s" and added a volume automatically.</info>', basename( getcwd() ) ) );
			$volumes[]            = sprintf( '%s:/var/www/html/wp-content/themes/%s', getcwd(), basename( getcwd() ) );
			$mapped_automatically = 'theme';
		}

		$parsed_volumes = [];

		$volumes = array_map( 'trim', $volumes );

		foreach ( $volumes as $volume ) {
			$v = explode( ':', $volume );
			if ( count( $v ) !== 2 && count( $v ) !== 3 ) {
				throw new \RuntimeException(
					'Invalid volume mapping format in "' . $volume . '". ' .
					'Expected format is either "/source/path:/destination/path" or ' .
					'"/source/path:/destination/path:flags".'
				);
			}
			if ( ! file_exists( $v[0] ) ) {
				throw new \RuntimeException(
					'The source path for the volume does not exist: "' . $v[0] . '". ' .
					'Please ensure the path is correct and accessible.'
				);
			}
			if ( substr( $v[1], 0, 1 ) !== '/' ) {
				throw new \RuntimeException(
					'The destination path must be an absolute Unix path, starting with "/". ' .
					'Found invalid destination path: "' . $v[1] . '".'
				);
			}

			if ( array_key_exists( $v[1], $parsed_volumes ) ) {
				if ( $mapped_automatically === 'plugin' ) {
					$this->output->writeln( sprintf( '<comment>Plugin directory "%s" detected and volume mapped automatically. No manual volume specification needed.</comment>', basename( getcwd() ) ) );
				} elseif ( $mapped_automatically === 'theme' ) {
					$this->output->writeln( sprintf( '<comment>Theme directory "%s" detected and volume mapped automatically. No manual volume specification needed.</comment>', basename( getcwd() ) ) );
				} else {
					$this->output->writeln( sprintf( '<comment>Warning: Volume "%s" already exists, skipping.</comment>', $v[1] ) );
				}
				continue;
			}

			// Add "read-only" flag to all local volumes outside of $config_dir.
			if ( strpos( normalize_path( $v[0] ), Config::get_qit_dir() ) !== 0 ) {
				$v[2] = 'ro';
			}

			$allowed_flags = [ 'ro', 'rw', 'delegated', 'cached', 'nocopy', 'z', 'Z' ];

			if ( count( $v ) === 3 ) {
				$flag = $v[2];
				if ( ! in_array( $flag, $allowed_flags, true ) ) {
					throw new \RuntimeException(
						'Invalid volume flag "' . $flag . '". ' .
						'Allowed flags are: ' . implode( ', ', $allowed_flags )
					);
				}
			}

			if ( count( $v ) === 3 ) {
				// [in-container]:[flags] = [host]
				$parsed_volumes[ $v[1] . ':' . $v[2] ] = $v[0];
			} else {
				// [in-container] = [host]
				$parsed_volumes[ $v[1] ] = $v[0];
			}
		}

		return $parsed_volumes;
	}
}
