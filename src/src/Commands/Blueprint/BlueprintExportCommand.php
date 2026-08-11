<?php

namespace QIT_CLI\Commands\Blueprint;

use QIT_CLI\Blueprints\BlueprintExporter;
use QIT_CLI\Commands\QITCommand;
use QIT_CLI\QITInput;
use QIT_CLI\RequestBuilder;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Emits a Playground Blueprint that reproduces a qit.json environment block,
 * so a QIT environment can be shared as a Playground link.
 */
class BlueprintExportCommand extends QITCommand {
	protected static $defaultName = 'blueprint:export'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected function configure(): void {
		parent::configure();

		$this->setDescription( 'Exports a qit.json environment as a WordPress Playground Blueprint.' )
			->addOption( 'environment', 'e', InputOption::VALUE_OPTIONAL, 'Environment block to export', 'default' )
			->addOption( 'output', 'O', InputOption::VALUE_OPTIONAL, 'Write the Blueprint to this file instead of stdout' )
			->addOption( 'skip-download-check', null, InputOption::VALUE_NONE, 'Do not check that pinned wordpress.org downloads exist' )
			->setHelp( <<<'HELP'
Export the current environment as a Blueprint:

  <info>qit blueprint:export</info>
  <info>qit blueprint:export --environment=legacy --output=blueprint.json</info>

The export is lossy: Docker volumes, local paths, PHP extensions and Xdebug
have no Playground equivalent and are reported as warnings.
HELP
			);
	}

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		$env_name   = (string) ( $input->getOption( 'environment' ) ?: 'default' );
		$env_config = $this->get_environment_config( $env_name );

		if ( empty( $env_config ) ) {
			$output->writeln( sprintf( '<error>No environment "%s" found in qit.json.</error>', $env_name ) );

			return self::FAILURE;
		}

		$exporter  = new BlueprintExporter();
		$blueprint = $exporter->export( $env_config );

		if ( ! $input->getOption( 'skip-download-check' ) ) {
			$blueprint = $this->drop_undownloadable_steps( $blueprint, $output );
		}

		$json = json_encode( $blueprint, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . "\n";

		$output_file = $input->getOption( 'output' );

		if ( $output_file ) {
			if ( file_put_contents( $output_file, $json ) === false ) {
				$output->writeln( sprintf( '<error>Could not write to %s</error>', $output_file ) );

				return self::FAILURE;
			}
			$output->writeln( sprintf( '<info>Wrote %s</info>', $output_file ) );
		} else {
			$output->write( $json );
		}

		foreach ( $exporter->get_warnings() as $warning ) {
			$output->writeln( sprintf( '<comment>Warning: %s</comment>', $warning ) );
		}

		return self::SUCCESS;
	}

	/**
	 * Remove install steps whose download does not exist on wordpress.org.
	 *
	 * Two ways to end up here. A pinned version that is not a real release tag
	 * ("8.5" where wordpress.org serves "8.5.0"), and a slug that is not a
	 * wordpress.org extension at all — a bare slug in qit.json defaults to wporg,
	 * but WooCommerce.com extensions resolve through a marketplace QIT can reach
	 * and Playground cannot.
	 *
	 * Either way the Blueprint would die on that step, so the step is dropped and
	 * the reason reported. A Blueprint that boots without a paid extension beats
	 * one that boots not at all.
	 *
	 * @param array<string, mixed> $blueprint The exported Blueprint.
	 *
	 * @return array<string, mixed>
	 */
	private function drop_undownloadable_steps( array $blueprint, OutputInterface $output ): array {
		$kept = [];

		foreach ( $blueprint['steps'] as $step ) {
			$resource = $step['pluginData'] ?? $step['themeData'] ?? null;

			if ( ! is_array( $resource ) ) {
				$kept[] = $step;
				continue;
			}

			$type = isset( $step['pluginData'] ) ? 'plugin' : 'theme';
			$url  = (string) ( $resource['url'] ?? '' );
			$slug = (string) ( $resource['slug'] ?? '' );

			// A wordpress.org resource resolves to the latest release; that is the
			// URL Playground will ask for.
			if ( $url === '' && $slug !== '' ) {
				$url = sprintf( 'https://downloads.wordpress.org/%s/%s.latest-stable.zip', $type, $slug );
			}

			if ( strpos( $url, 'https://downloads.wordpress.org/' ) !== 0 || $this->is_downloadable( $url ) ) {
				$kept[] = $step;
				continue;
			}

			$output->writeln( sprintf(
				'<comment>Warning: dropped %s "%s" — %s is not downloadable. Either the version is not a real release tag (wordpress.org serves "8.5.0", not "8.5"), or it is not a wordpress.org extension and Playground cannot install it.</comment>',
				$type,
				$slug !== '' ? $slug : basename( $url ),
				$url
			) );
		}

		$blueprint['steps'] = $kept;

		return $blueprint;
	}

	private function is_downloadable( string $url ): bool {
		try {
			( new RequestBuilder( $url ) )
				->with_method( 'HEAD' )
				->with_timeout_in_seconds( 10 )
				->with_expected_status_codes( [ 200 ] )
				->request();

			return true;
		} catch ( \Exception $e ) {
			return false;
		}
	}
}
