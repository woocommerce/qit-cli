<?php

use PHPUnit\Framework\TestCase;
use QIT_CLI\App;
use QIT_CLI\Environment\Extension;
use QIT_CLI\Environment\PluginsAndThemesParser;
use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\Serializer;

class PluginsAndThemesParserTest extends TestCase {
	use MatchesSnapshots;

	/** @var PluginsAndThemesParser */
	protected $sut;

	/** @var string */
	protected $test_tag_local_directory;

	/** @var string */
	protected $test_tag_local_zip;

	public function setUp(): void {
		parent::setUp();

		App::when( PluginsAndThemesParser::class )
		   ->needs( OutputInterface::class )
		   ->give( NullOutput::class );

		$this->sut = App::make( PluginsAndThemesParser::class );

		$this->test_tag_local_directory = sys_get_temp_dir() . '/test-tag-local-dir';
		mkdir( $this->test_tag_local_directory );
		$this->test_tag_local_zip = sys_get_temp_dir() . '/test-tag-local-zip.zip';
		touch( $this->test_tag_local_zip );
	}

	protected function tearDown(): void {
		rmdir( $this->test_tag_local_directory );
		unlink( $this->test_tag_local_zip );
		parent::tearDown();
	}

	public function test_parse_extensions_array() {
		$json = <<<JSON
{
  "plugins": {
    "qit-beaver": {
      "source": "https://github.com/qitbeaver/qit-beaver",
      "test_tags": ["stable", "{$this->test_tag_local_directory}"],
      "action": "bootstrap"
    },
    "qit-cat": {
      "source": "https://github.com/cat/pictures",
      "test_tags": ["beta", "release-candidate"],
      "action": "test"
    }
  }
}
JSON;

		$yml        = <<<YML
plugins:
  qit-beaver:
    source: https://github.com/qitbeaver/qit-beaver
    test_tags:
      - stable
      - "{$this->test_tag_local_directory}"
    action: bootstrap
  qit-cat:
    source: https://github.com/cat/pictures
    test_tags:
      - beta
      - release-candidate
    action: test
  
YML;
		$yaml_array = App::make( Serializer::class )->decode( $yml, 'yaml' );
		$json_array = App::make( Serializer::class )->decode( $json, 'json' );

		$this->assertEquals( $json_array, $yaml_array );

		$this->assertMatchesJsonSnapshot( $this->sut->parse_extensions( $yaml_array['plugins'], Extension::TYPES['plugin'] ) );
	}

	public function test_parse_extensions_option() {
		/*
		 * Equivalent to:
		 * --plugin qit-beaver
		 * --plugin qit-cat:test:rc,feature-foo
		 */
		$cli_array = [
			'plugins' => [
				'qit-beaver',
				'qit-cat:test:rc',
				'qit-cat:test:rc,feature-foo',
				'qit-cat:test:rc, feature-foo',
				'qit-cat:test:rc, feature-foo, ',
				'{"slug": "qit-beaver", "source": "https://github.com/qitbeaver/qit-beaver", "test_tags": ["stable", "beta"], "action": "bootstrap"}',
				'{"slug": "qit-cat", "source": "https://github.com/cat/pictures", "action": "test"}',
				'{"slug": "dog-pictures", "source": "https://github.com/cat/pictures?foo=bar", "action": "bootstrap"}',
				'{"slug": "dog-pictures", "source": "https://github.com/cat/pictures"}',
				'{"slug":"qit-beaver","source":"https://github.com/qitbeaver/qit-beaver","test_tags":["stable","' . $this->test_tag_local_zip . '"],"action":"bootstrap"}',
			],
		];

		$this->assertMatchesJsonSnapshot( $this->sut->parse_extensions( $cli_array['plugins'], Extension::TYPES['plugin'] ) );
	}

	public function test_parse_extensions_infer() {
		$cli_array = [
			'plugins' => [
				'qit-beaver',
				'qit-cat:test:rc',
				'qit-cat:test:rc,feature-foo',
				'qit-cat:test:rc, feature-foo',
				'qit-cat:test:rc, feature-foo, ',
				"qit-cat:test:rc,{$this->test_tag_local_zip},",
				'https://github.com/qitbeaver/foo-extension.zip:test:rc,feature-foo', // URL, short syntax.
				"https://github.com/qitbeaver/foo-extension.zip:test:{$this->test_tag_local_directory},foobarbaz", // URL, short syntax.
				'/path/to/file/bar-extension.zip:test:rc,feature-foo', // File path, short syntax.
				'/path/to/file/baz-extension:test:rc,feature-foo', // Directory, short syntax.
				'/path/to/file/bar-extension.zip', // File, short syntax, defaults.
				'C:\\Program Files\\example\\qit-beaver.zip',  // Windows file path.
				'ftp://ftp.example.com/qit-beaver.zip',  // We don't really support this, but just in case.
				'ssh://example.com:/path/to/qit-beaver.zip', // We don't really support this, but just in case.
				'\\\\network-share\\plugins\\qit-beaver.zip', // We don't really support this, but just in case.
				'{"source":"https://github.com/qitbeaver/foo-extension.zip"}', // If they want to, just use JSON.
				'{"source":"/path/to/file/bar-extension.zip"}', // If they want to, just use JSON.
				'{"source":"/path/to/file/baz-extension"}', // If they want to, just use JSON.
			],
		];

		$this->assertMatchesJsonSnapshot( $this->sut->parse_extensions( $cli_array['plugins'], Extension::TYPES['plugin'] ) );
	}
}