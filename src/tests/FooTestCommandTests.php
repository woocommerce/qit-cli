<?php

namespace QIT_CLI_Tests;

use QIT_CLI\Commands\QITCommand;
use QIT_CLI\TestConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

// Configuration class for foo tests
class FooTestConfig extends TestConfig {
	public function getTestType(): string {
		return 'foo';
	}
}

// Command class for executing foo tests
class FooTestCommand extends QITCommand {
	protected function configure(): void {
		parent::configure();
		$this->setName('foo:test')
		     ->setDescription('Run a foo test')
		     ->addOption('param', null, InputOption::VALUE_OPTIONAL, 'Test parameter', 'default');
	}

	protected function doExecute(InputInterface $input, OutputInterface $output): int {
		$tests = $this->config->get('tests', []);
		$fooTests = $tests['foo'] ?? [];
		$testConfig = new FooTestConfig($fooTests);
		$param = $input->getOption('param');
		$output->writeln('Running ' . $testConfig->getTestType() . ' test with param: ' . $param);
		$output->writeln('Test config: ' . json_encode($testConfig->getConfig()));
		return Command::SUCCESS;
	}
}

// Test class for FooTestCommand
class FooTestCommandTests extends QITTestCase {
	use MatchesSnapshots;

	public function tearDown(): void {
		// Clean up temporary files
		if (file_exists('qit.json')) {
			unlink('qit.json');
		}
		parent::tearDown();
	}

	public function test_generates_foo_test_config() {
		file_put_contents('qit.json', json_encode(['tests' => ['foo' => ['param' => 'value']]]));

		$command = new FooTestCommand();
		$tester = new CommandTester($command);
		$tester->execute([]);

		$this->assertStringContainsString('Running foo test with param: default', $tester->getDisplay());
		$this->assertStringContainsString('Test config: {"param":"value"}', $tester->getDisplay());
		$this->assertEquals(Command::SUCCESS, $tester->getStatusCode());
	}

	public function test_overrides_param() {
		file_put_contents('qit.json', json_encode(['tests' => ['foo' => ['param' => 'value']]]));

		$command = new FooTestCommand();
		$tester = new CommandTester($command);
		$tester->execute(['--param' => 'custom']);

		$this->assertStringContainsString('Running foo test with param: custom', $tester->getDisplay());
		$this->assertStringContainsString('Test config: {"param":"value"}', $tester->getDisplay());
		$this->assertEquals(Command::SUCCESS, $tester->getStatusCode());
	}

	public function test_handles_missing_test_config() {
		file_put_contents('qit.json', json_encode(['other' => 'data']));

		$command = new FooTestCommand();
		$tester = new CommandTester($command);
		$tester->execute([]);

		$this->assertStringContainsString('Running foo test with param: default', $tester->getDisplay());
		$this->assertStringContainsString('Test config: []', $tester->getDisplay());
		$this->assertEquals(Command::SUCCESS, $tester->getStatusCode());
	}
}