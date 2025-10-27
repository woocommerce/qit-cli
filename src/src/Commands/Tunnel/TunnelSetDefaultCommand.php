<?php

namespace QIT_CLI\Commands\Tunnel;

use QIT_CLI\Commands\QITCommand;
use QIT_CLI\Cache;
use QIT_CLI\QITInput;
use QIT_CLI\Tunnel\TunnelRunner;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class TunnelSetDefaultCommand extends QITCommand {
	protected static $defaultName = 'tunnel:set-default'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected Cache $cache;

	public function __construct( Cache $cache ) {
		parent::__construct();
		$this->cache = $cache;
	}

	protected function configure(): void {
		parent::configure();
		$this
			->setDescription( 'Set the default tunneling method for QIT CLI.' )
			->setHelp( 'Allows you to set your preferred default tunneling method.' );
	}

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		$available_methods = array_keys( TunnelRunner::$tunnel_map );

		$usable_methods = [];
		foreach ( $available_methods as $method ) {
			$tunnel_class = TunnelRunner::get_tunnel_class( $method );

			if ( ! $tunnel_class ) {
				continue;
			}

			try {
				$tunnel_class::check_is_installed();
			} catch ( \Exception $e ) {
				continue;
			}

			if ( $tunnel_class::is_configured() ) {
				$usable_methods[ $method ] = $method;
			}
		}

		if ( empty( $usable_methods ) ) {
			$output->writeln( '<error>No usable and configured tunneling methods are available on this system.</error>' );
			return self::FAILURE;
		}

		$helper   = $this->getHelper( 'question' );
		$question = new ChoiceQuestion(
			'Select the tunneling method you wish to set as default:',
			$usable_methods,
			key( $usable_methods )
		);
		$question->setErrorMessage( 'Method %s is invalid.' );

		$default_method = $helper->ask( $input, $output, $question );

		$this->cache->set( 'tunnel_default', $default_method, -1 );

		$output->writeln( '<info>Default tunneling method set to: ' . $default_method . '</info>' );

		return self::SUCCESS;
	}
}
