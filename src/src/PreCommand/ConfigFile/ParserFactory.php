<?php

namespace QIT_CLI\PreCommand\ConfigFile;

use QIT_CLI\App;
use QIT_CLI\PreCommand\ConfigFile;
use QIT_CLI\PreCommand\ConfigFile\Parsers\AbstractConfigParser;

class ParserFactory {
	public function get_parser( string $key ): AbstractConfigParser {
		$parsers = [
			'$schema'              => ConfigFile\Parsers\SimpleValueParser::class,
			'slug'                 => ConfigFile\Parsers\SimpleValueParser::class,
			'type'                 => ConfigFile\Parsers\SimpleValueParser::class,
			'pre_test_build'       => ConfigFile\Parsers\PreTestBuildParser::class,
			'environments'         => ConfigFile\Parsers\EnvironmentParser::class,
			'tests'                => ConfigFile\Parsers\TestParser::class,
			'custom_test_packages' => ConfigFile\Parsers\CustomTestPackageParser::class,
			'groups'               => ConfigFile\Parsers\GroupParser::class,
		];

		if ( ! isset( $parsers[ $key ] ) ) {
			throw new \RuntimeException( "No parser found for configuration key '$key'." );
		}

		return App::make( $parsers[ $key ] );
	}
}