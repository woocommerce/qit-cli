<?php

namespace QIT_CLI\Commands;

use QIT_CLI\QITInput;
use Symfony\Component\Console\Output\OutputInterface;

class RunWooE2ETestCommand extends RunE2ECommand {
	use ExtensionSetTrait;

	protected static $defaultName = 'run:woo-e2e'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase
	protected string $test_type   = 'e2e';

	/**
	 * Used when the Manager offers nothing for the requested WooCommerce version,
	 * which covers a Manager that predates the lookup table as well as a version
	 * no published package covers.
	 */
	private const FALLBACK_TEST_PACKAGE = 'woocommerce/core-e2e-tests:latest';

	protected function configure(): void {
		parent::configure();
		$this->setDescription( 'Run WooCommerce Core E2E tests' );
		$this->configure_extension_set_option();
	}

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		$remote_result = $this->run_remote_extension_set_if_provided( $input, $output, 'woo-e2e', 'woo-e2e' );
		if ( $remote_result !== null ) {
			return $remote_result;
		}

		$extension_set_error = $this->resolve_extension_set( $input, $output );
		if ( $extension_set_error !== null ) {
			return $extension_set_error;
		}

		if ( empty( $input->getOption( 'test-package' ) ) ) {
			$input->setOption( 'test-package', [ $this->resolve_test_package( $input, $output ) ] );
		}

		return parent::doExecute( $input, $output );
	}

	/**
	 * The Woo Core E2E package covering the WooCommerce version this run asks for.
	 *
	 * Nothing is worked out here on purpose. The Manager resolves it and hands
	 * over a lookup table in sync data, keyed by exactly what `--woo` accepts,
	 * channel names included: which package versions exist is the Manager's to
	 * know, and a second implementation of the rule would drift from it.
	 *
	 * This used to be a single hardcoded `latest` for every run, so a run on one
	 * WooCommerce version executed the same specs as a run on any other.
	 */
	protected function resolve_test_package( QITInput $input, OutputInterface $output ): string {
		/*
		 * A run that names no version is assumed to mean `stable`, and that is an
		 * assumption rather than something observed. Nothing here asks the
		 * environment what it will install, and nothing can: the package supplies
		 * mu-plugins and a globalSetup phase that go into building the environment,
		 * so it has to be chosen before `env:up` reports a version.
		 *
		 * It holds because two independent sources agree on what "latest stable"
		 * is. The Manager derives `stable` from the WooCommerce GitHub releases,
		 * while an unpinned environment installs WooCommerce to satisfy the
		 * package manifest's `requires.plugins`, which resolves to the wp.org
		 * stable tag. They can disagree for a few hours around a release, and a
		 * run started in that window gets the package for the previous version.
		 */
		$requested = (string) ( $input->getOption( 'woocommerce_version' ) ?: 'stable' );

		try {
			$by_version = $this->cache->get_manager_sync_data( 'test_package_versions' );
		} catch ( \Throwable $e ) {
			// Absent on a Manager that does not publish the table yet.
			return self::FALLBACK_TEST_PACKAGE;
		}

		$test_package = is_array( $by_version ) ? ( $by_version['e2e'][ $requested ] ?? null ) : null;

		if ( ! is_string( $test_package ) || $test_package === '' ) {
			return self::FALLBACK_TEST_PACKAGE;
		}

		$output->writeln( sprintf(
			'<comment>Using test package %s for WooCommerce %s.</comment>',
			$test_package,
			$requested
		) );

		return $test_package;
	}
}
