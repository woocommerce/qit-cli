<?php

use Dotenv\Dotenv;
use lucatume\DI52\App;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

$dotenv = Dotenv::createImmutable( __DIR__ );
$dotenv->load();
$dotenv->required( [
	'QIT_CUSTOM_TESTS_USER',
	'QIT_CUSTOM_TESTS_USER_QIT_TOKEN',
	'QIT_CUSTOM_TESTS_SECRET',
	'QIT_CUSTOM_TESTS_URL',
	'QIT_CUSTOM_TESTS_ENV',
] );

function init() {
	// Delete the "tmp_qit_config" directory if it exists.
	$fs = new Filesystem();
	if ( $fs->exists( __DIR__ . '/tmp_qit_config' ) ) {
		$fs->remove( __DIR__ . '/tmp_qit_config' );
	}

	// Enable dev mode.
	$dev = new Process( [ App::getVar( 'qit' ), 'dev' ] );
	$dev->setEnv( [ 'QIT_HOME' => __DIR__ . '/tmp_qit_config' ] );
	$dev->mustRun();

	// Add the environment.
	$add_environment = new Process( [
		App::getVar( 'qit' ),
		'backend:add',
		'--manager_url',
		$_ENV['QIT_CUSTOM_TESTS_URL'],
		'--qit_secret',
		$_ENV['QIT_CUSTOM_TESTS_SECRET'],
		'--environment',
		$_ENV['QIT_CUSTOM_TESTS_ENV'],
	] );
	$add_environment->setEnv( [ 'QIT_HOME' => __DIR__ . '/tmp_qit_config' ] );
	$add_environment->mustRun();

	// Add the partner account that will be used.
	$add_partner = new Process( [
		App::getVar( 'qit' ),
		'partner:add',
		'--user',
		$_ENV['QIT_CUSTOM_TESTS_USER'],
		'--qit_token',
		$_ENV['QIT_CUSTOM_TESTS_USER_QIT_TOKEN'],
	] );
	$add_partner->setEnv( [ 'QIT_HOME' => __DIR__ . '/tmp_qit_config' ] );
	$add_partner->mustRun();
}

function qit( array $command, int $expected_exit_code = 0 ): string {
	$args = [ __DIR__ . '/../../qit' ];
	$args = array_merge( $args, $command );
	$qit  = new Process( $args );
	$qit->setEnv( [ 'QIT_HOME' => __DIR__ . '/tmp_qit_config' ] );
	$qit->run();

	if ( $qit->getExitCode() !== $expected_exit_code ) {
		throw new \RuntimeException( sprintf( "Command \"%s\" failed with exit code %d. \n\nError Output:\n %s \n\nOutput:\n %s", implode( ' ', $command ), $qit->getExitCode(), $qit->getErrorOutput(), $qit->getOutput() ) );
	}

	return $qit->getOutput();
}