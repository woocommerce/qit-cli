<?php

namespace QIT_CLI\Commands;

use QIT_CLI\PreCommand\Interfaces\ConfigurableTestCommand;
use Symfony\Component\Console\Input\InputInterface;

abstract class DynamicCommand extends QITCommand implements ConfigurableTestCommand {
	/** @var array<mixed> $options_to_send */
	protected $options_to_send = [];

	/** @var string $test_type The test type for this dynamic command */
	protected $test_type;

	public function __construct( string $test_type = null ) {
		$this->test_type = $test_type;
		parent::__construct();
	}

	// ConfigurableTestCommand interface implementation
	public function get_test_type(): string {
		return $this->test_type;
	}

	public function get_test_profile(): string {
		return $this->input->getOption( 'profile' ) ?? 'default';
	}

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
			if ( $option->isValueRequired() && ( is_null( $value ) || $value === '' ) ) {
				throw new \InvalidArgumentException( sprintf( 'The required option "%s" is not set. Run the command with --help for more information.', $name ) );
			}
		}
	}

	/**
	 * @param InputInterface $input
	 *
	 * @return array<string,scalar|array<scalar>> The keys are option names, the values are the option values. It can be boolean if option is boolean, scalar if it's a value, or array if option is an array.
	 */
	protected function parse_options( InputInterface $input, bool $filter_to_send = true ): array {
		$this->validate_required_options( $input );

		// Filter from the available options, those that we want to send.
		if ( $filter_to_send ) {
			$options = array_intersect_key( $input->getOptions(), $this->options_to_send );
		} else {
			$options = $input->getOptions();
		}

		// Filter empty options without a default.
		$options = array_filter( $options, static function ( $v ) {
			return ! is_null( $v );
		} );

		return $options;
	}
}
