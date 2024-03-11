<?php

namespace QIT_CLI\Commands;

use QIT_CLI\App;
use QIT_CLI\IO\Output;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

abstract class DynamicCommandCreator {
	abstract public function register_commands( Application $application ): void;

	/**
	 * @param DynamicCommand $command The command to add the schema to.
	 * @param array<mixed>   $schema The schema to add to the command.
	 * @param array<string>  $exceptions What to ignore when adding options.
	 * @param array<string>  $whitelist If set, only these options will be added.
	 *
	 * @return void
	 */
	public static function add_schema_to_command( Command $command, array $schema, array $exceptions = [], array $whitelist = [] ): void {
		if ( ! empty( $schema['description'] ) ) {
			$command->setDescription( $schema['description'] );
		}

		if ( ! empty( $schema['properties'] ) && is_array( $schema['properties'] ) ) {
			foreach ( $schema['properties'] as $property_name => $property_schema ) {
				$ignore = [ 'client', 'event', 'woo_id', 'is_product_update', 'upload_id' ];

				if ( in_array( $property_name, array_merge( $exceptions, $ignore ), true ) ) {
					continue;
				}

				if ( ! empty( $whitelist ) && ! in_array( $property_name, $whitelist, true ) ) {
					continue;
				}

				if ( isset( $property_schema['required'] ) && $property_schema['required'] ) {
					$required = InputOption::VALUE_REQUIRED;
				} else {
					$required = InputOption::VALUE_OPTIONAL;
				}

				$description = $property_schema['description'] ?? '';
				$default     = $property_schema['default'] ?? null;

				$items = $property_schema['items'] ?? '';
				$enum  = $property_schema['enum'] ?? '';

				if ( empty( $enum ) && ! empty( $items ) ) {
					if ( is_array( $items ) ) {
						foreach ( $items as $type => $type_schemas ) {
							if ( ! in_array( $type, [ 'oneOf', 'anyOf' ], true ) ) {
								continue;
							}
							foreach ( $type_schemas as $type_schema ) {
								if ( is_array( $type_schema ) && ! empty( $type_schema['enum'] ) ) {
									$enum = $type_schema['enum'];
									break;
								}
							}
						}
					}
				}

				if ( $required === InputOption::VALUE_OPTIONAL && ! empty( $description ) ) {
					$description = '(Optional) ' . $description;
				}

				if ( ! empty( $enum ) ) {
					// Convert an array to a comma-separated list.
					if ( is_array( $enum ) ) {
						$enum = implode( ', ', $enum );
					}

					// If $enum is as expected, show the possible values.
					if ( is_scalar( $enum ) ) {
						// Show up to 200 characters of possible values.
						$enum        = substr( $enum, 0, 200 );
						$description = sprintf( '%s <comment>[possible values: %s]</comment>', $description, $enum );
					}
				}

				if ( App::make( Output::class )->isVerbose() ) {
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
