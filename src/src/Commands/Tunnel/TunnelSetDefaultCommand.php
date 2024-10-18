<?php

namespace QIT_CLI\Commands\Tunnel;

use Symfony\Component\Console\Command\Command;
use QIT_CLI\Cache;
use QIT_CLI\Tunnel\TunnelRunner;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class TunnelSetDefaultCommand extends Command {
	protected static $defaultName = 'tunnel:set-default';

	/** @var Cache */
	protected $cache;

	public function __construct( Cache $cache ) {
		parent::__construct();
		$this->cache = $cache;
	}

	protected function configure() {
		$this
			->setDescription( 'Set the default tunneling method for QIT CLI.' )
			->setHelp( 'Allows you to set your preferred default tunneling method.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$available_methods = array_keys( TunnelRunner::$tunnel_map );

		$usable_methods = [];
		foreach ( $available_methods as $method ) {
			$tunnel_class = TunnelRunner::get_tunnel_class( $method );
			if ( $tunnel_class && $tunnel_class::is_supported() && $tunnel_class::is_configured( $this->cache ) ) {
				$usable_methods[ $method ] = $method;
			}
		}

		if ( empty( $usable_methods ) ) {
			$output->writeln( '<error>No usable and configured tunneling methods are available on this system.</error>' );
			return Command::FAILURE;
		}

		$helper = $this->getHelper( 'question' );
		$question = new ChoiceQuestion(
			'Select the tunneling method you wish to set as default:',
			$usable_methods,
			key( $usable_methods )
		);
		$question->setErrorMessage( 'Method %s is invalid.' );

		$method = $helper->ask( $input, $output, $question );

		// Save the default method
		$this->cache->set( 'tunnel_default', $method, -1 );
		$output->writeln( '<info>Default tunneling method set to: ' . $method . '</info>' );

		return Command::SUCCESS;
	}
}
