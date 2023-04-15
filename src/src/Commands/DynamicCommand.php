<?php

namespace QIT_CLI\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class DynamicCommand extends Command {
	/** @var array<mixed> $options_to_send */
	protected $options_to_send = [];

	public function add_option_to_send( string $option_name ): void {
		$this->options_to_send[ $option_name ] = '';
	}

	/**
	 * Symfony considers options as optional, and only enforces
	 * the "required" if the option is used but no value is passed.
	 *
	 * This method changes this behavior to ensure that a required option
	 * needs to be passed, otherwise the command is not executed.
	 *
	 * @throws \InvalidArgumentException When a required option is empty.
	 */
	protected function validate_required_options( InputInterface $input ): void {
		$options = $this->getDefinition()->getOptions();
		foreach ( $options as $option ) {
			$name  = $option->getName();
			$value = $input->getOption( $name );
			if ( $option->isValueRequired() && empty( $value ) ) {
				throw new \InvalidArgumentException( sprintf( 'The required option "%s" is not set. Run the command with --help for more information.', $name ) );
			}
		}
	}

	protected function parse_options( InputInterface $input, OutputInterface $output ): array {
		$this->validate_required_options( $input );

		// Filter from the available options, those that we want to send.
		$options = array_intersect_key( $input->getOptions(), $this->options_to_send );

		// Filter empty options without a default.
		$options = array_filter( $options, static function ( $v ) {
			return ! is_null( $v );
		} );

		return $options;
	}
}
