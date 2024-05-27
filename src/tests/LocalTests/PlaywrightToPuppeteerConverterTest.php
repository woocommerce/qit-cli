<?php

namespace QIT_CLI_Tests\LocalTests;


use QIT_CLI\App;
use QIT_CLI\LocalTests\PlaywrightToPuppeteerConverter;
use QIT_CLI_Tests\QITTestCase;
use Spatie\Snapshots\MatchesSnapshots;

class PlaywrightToPuppeteerConverterTest extends QITTestCase {
	use MatchesSnapshots;

	public function test_convert_playwright_to_puppeteer() {
		$playwright_report = json_decode( file_get_contents( __DIR__ . '/../data/playwright-result.json' ), true );

		$this->assertMatchesJsonSnapshot( App::make( PlaywrightToPuppeteerConverter::class )->convert_pw_to_puppeteer( $playwright_report ) );
	}

	public function test_convert_playwright_multiple_to_puppeteer() {
		$playwright_report = json_decode( file_get_contents( __DIR__ . '/../data/playwright-result-multiple.json' ), true );

		$this->assertMatchesJsonSnapshot( App::make( PlaywrightToPuppeteerConverter::class )->convert_pw_to_puppeteer( $playwright_report ) );
	}
}