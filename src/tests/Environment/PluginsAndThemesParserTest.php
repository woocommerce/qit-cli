<?php

use PHPUnit\Framework\TestCase;
use QIT_CLI\App;
use QIT_CLI\Environment\PluginsAndThemesParser;
use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

class PluginsAndThemesParserTest extends TestCase {
	use MatchesSnapshots;

	/** @var PluginsAndThemesParser */
	protected $sut;

	public function setUp(): void {
		parent::setUp();

		App::when( PluginsAndThemesParser::class )
		   ->needs( OutputInterface::class )
		   ->give( NullOutput::class );

		$this->sut = App::make( PluginsAndThemesParser::class );
	}

	/*
	public function test_parse_extensions_string() {
		$this->assertMatchesJsonSnapshot( $this->sut->parse_extensions( [
			'qit-beaver',
			'cat-pictures',
		] ) );
	}
	*/

	public function test_parse_extensions_array() {
		$json = <<<'JSON'
{
  "plugins": [
    {
      "slug": "qit-beaver",
      "source": "https://github.com/qitbeaver/qit-beaver",
      "test_tags": ["stable", "/path/to/local/test"],
      "action": "bootstrap"
    },
    {
      "slug": "cat-pictures",
      "source": "https://github.com/cat/pictures",
      "test_tags": ["beta", "release-candidate"],
      "action": "test"
    },
    "{\"slug\": \"dog-pictures\", \"source\": \"https://github.com/cat/pictures?foo=bar\", \"action\": \"test\"}"
  ]
}
JSON;

		$yml        = <<<'YML'
plugins:
  - slug: qit-beaver
    source: "https://github.com/qitbeaver/qit-beaver"
    test_tags: ["stable", "/path/to/local/test"]
    action: "bootstrap"
  - slug: cat-pictures
    source: "https://github.com/cat/pictures"
    test_tags: ["beta", "release-candidate"]
    action: "test"
  - '{"slug": "dog-pictures", "source": "https://github.com/cat/pictures?foo=bar", "action": "test"}'
YML;
		$yaml_array = App::make( \Symfony\Component\Serializer\Serializer::class )->decode( $yml, 'yaml' );
		$json_array = App::make( \Symfony\Component\Serializer\Serializer::class )->decode( $json, 'json' );

		$this->assertEquals( $json_array, $yaml_array );

		$this->assertMatchesJsonSnapshot( $this->sut->parse_extensions( $yaml_array['plugins'] ) );
	}

	public function test_parse_extensions_option() {
		/*
		 * Equivalent to:
		 * --plugin qit-beaver \
		 * --plugin qit-cat:test:rc,feature-foo \
		 * --plugin "slug=qit-beaver,source=https://github.com/qitbeaver/qit-beaver,test_tags=stable|beta,action=test-action" \
		 * --plugin "slug=cat-pictures,source=https://github.com/cat/pictures,test_tags=,action=deploy-action"
		 */
		$cli_array = [
			'plugins' => [
				'qit-beaver',
				'qit-cat:test:rc',
				'qit-cat:test:rc,feature-foo',
				'qit-cat:test:rc, feature-foo',
				'qit-cat:test:rc, feature-foo, ',
				'{"slug": "qit-beaver", "source": "https://github.com/qitbeaver/qit-beaver", "test_tags": ["stable", "beta"], "action": "bootstrap"}',
				'{"slug": "cat-pictures", "source": "https://github.com/cat/pictures", "action": "test"}',
				'{"slug": "dog-pictures", "source": "https://github.com/cat/pictures?foo=bar", "action": "bootstrap"}',
				'{"slug": "dog-pictures", "source": "https://github.com/cat/pictures"}',
			],
		];

		$this->assertMatchesJsonSnapshot( $this->sut->parse_extensions( $cli_array['plugins'] ) );
	}
}