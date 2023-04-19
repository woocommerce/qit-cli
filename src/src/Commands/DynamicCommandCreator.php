<?php

namespace QIT_CLI\Commands;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class DynamicCommandCreator {
	abstract public function register_commands( Application $application ): void;

	/**
	 * @param DynamicCommand $command
	 * @param array<mixed>   $schema
	 *
	 * @return void
	 */
	protected function add_schema_to_command( DynamicCommand $command, array $schema ): void {
		if ( ! empty( $schema['description'] ) ) {
			$command->setDescription( $schema['description'] );
		}

		if ( ! empty( $schema['properties'] ) && is_array( $schema['properties'] ) ) {
			foreach ( $schema['properties'] as $property_name => $property_schema ) {
				$ignore = [ 'client', 'event', 'woo_id' ];

				if ( in_array( $property_name, $ignore, true ) ) {
					continue;
				}

				if ( isset( $property_schema['required'] ) && $property_schema['required'] ) {
					$required = InputOption::VALUE_REQUIRED;
				} else {
					$required = InputOption::VALUE_OPTIONAL;
				}

				$description = $property_schema['description'] ?? '';
				$default     = $property_schema['default'] ?? null;

				if ( $required === InputOption::VALUE_OPTIONAL && ! empty( $description ) ) {
					$description = '(Optional) ' . $description;
				}

				if ( isset( $this->output ) && $this->output instanceof OutputInterface && $this->output->isVerbose() ) {
					$schema_to_show = $property_schema;
					unset( $schema_to_show['description'] );
					$description = sprintf( '%s (Schema: %s)', $description, json_encode( $schema_to_show ) );
				}

				// So that we know inside the command that this option should be sent to the Manager.
				$command->add_option_to_send( $property_name );

				$command
					->addOption(
						$property_name,
						null,
						$required,
						$description,
						$default
					);
			}
		}
	}
}
