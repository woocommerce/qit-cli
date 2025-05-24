<?php

namespace QIT_CLI\PreCommand;

use QIT_CLI\App;

class ParserFactory {
	public function get_parser( string $key ): Parsers\AbstractConfigParser {
		$parsers = [
			'$schema'              => Parsers\SimpleValueParser::class,
			'slug'                 => Parsers\SimpleValueParser::class,
			'type'                 => Parsers\SimpleValueParser::class,
			'pre_test_build'       => Parsers\PreTestBuildParser::class,
			'environments'         => Parsers\EnvironmentParser::class,
			'tests'                => Parsers\TestParser::class,
			'custom_test_packages' => Parsers\CustomTestPackageParser::class,
			'groups'               => Parsers\GroupParser::class,
		];

		if ( ! isset( $parsers[ $key ] ) ) {
			throw new \RuntimeException( "No parser found for configuration key '$key'." );
		}

		return App::make( $parsers[ $key ] );
	}
}