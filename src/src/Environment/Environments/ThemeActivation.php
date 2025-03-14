<?php

namespace QIT_CLI\Environment\Environments;

use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Handles theme auto-activation logic for QIT E2E environments,
 * assuming each theme is an Extension object with a ->slug property.
 */
class ThemeActivation {
	/** @var E2EEnvInfo */
	protected $env_info;

	/** @var Docker */
	protected $docker;

	/** @var OutputInterface */
	protected $output;

	/**
	 * @param E2EEnvInfo      $env_info
	 * @param Docker          $docker
	 * @param OutputInterface $output
	 */
	public function __construct( E2EEnvInfo $env_info, Docker $docker, OutputInterface $output ) {
		$this->env_info = $env_info;
		$this->docker   = $docker;
		$this->output   = $output;
	}

	/**
	 * Entry point: Attempts to auto-activate themes with these rules:
	 *  1. If exactly 1 theme: activate it.
	 *  2. If exactly 2 themes and one references the other as a parent, activate the child only.
	 *  3. Otherwise, skip auto-activation.
	 *
	 * WP-CLI will handle errors if a parent is missing.
	 */
	public function auto_activate_themes(): void {
		$theme_slugs = array_map( static function ( $ext ) {
			return $ext->slug;
		}, $this->env_info->themes );

		$count = count( $theme_slugs );

		switch ( $count ) {
			case 0:
				return;

			case 1:
				$child_slug  = $theme_slugs[0];
				$parent_slug = $this->detect_parent_template( $child_slug );

				if ( $parent_slug ) {
					$this->output->writeln(
						"<comment>Theme '{$child_slug}' is a child of '{$parent_slug}'.</comment>"
					);

					try {
						$install_output = $this->docker->run_inside_docker(
							$this->env_info,
							[
								'bash',
								'-c',
								sprintf( 'wp theme install %s --force', escapeshellarg( $parent_slug ) ),
							]
						);

						$this->output->writeln( $install_output );
						$this->activate_single_theme( $child_slug );
					} catch ( \RuntimeException $e ) {
						$this->output->writeln(
							'<comment>Could not find the parent theme on WP.org. ' .
							'Skipping child activation. Please provide the parent theme as well.</comment>'
						);
					}
				} else {
					// Not a child => just activate.
					$this->activate_single_theme( $child_slug );
				}

				return;

			case 2:
				$this->maybe_activate_parent_child( $theme_slugs );

				return;

			default:
				$this->output->writeln(
					"<comment>Theme auto-activation skipped: {$count} themes provided.</comment>"
				);

				return;
		}
	}


	/**
	 * If exactly one slug, just run "wp theme activate".
	 * Let WP-CLI fail if the parent is missing.
	 */
	protected function activate_single_theme( string $theme_slug ): void {
		$this->output->writeln( "<info>Auto-activating single theme: {$theme_slug}</info>" );

		$activate_output = $this->docker->run_inside_docker(
			$this->env_info,
			[
				'bash',
				'-c',
				sprintf( 'wp theme activate %s', escapeshellarg( $theme_slug ) ),
			]
		);

		$this->output->writeln( $activate_output );
	}

	/**
	 * If we have exactly 2 themes, see if one references the other as "Template: parent".
	 * If so, activate the child only. Otherwise, skip.
	 *
	 * @param array<string> $slugs
	 */
	protected function maybe_activate_parent_child( array $slugs ): void {
		$this->output->writeln(
			'<info>Detected exactly 2 themes; checking for parent–child relationship.</info>'
		);

		// Parse style.css for each slug.
		$details = [];
		foreach ( $slugs as $slug ) {
			$details[ $slug ] = [
				'parent' => $this->detect_parent_template( $slug ),
			];
		}

		$activated_child = false;
		foreach ( $details as $child_slug => $child_info ) {
			if ( ! $child_info['parent'] ) {
				continue; // Not a child.
			}

			$parent_slug = strtolower( $child_info['parent'] );

			if (
				in_array( $parent_slug, $slugs, true ) &&
				$parent_slug !== $child_slug
			) {
				$this->output->writeln(
					"<comment>Parent–child detected. Activating child theme '{$child_slug}'.</comment>"
				);

				$this->activate_single_theme( $child_slug );
				$activated_child = true;
				break;
			}
		}

		if ( ! $activated_child ) {
			$this->output->writeln(
				'<comment>Two themes provided, but no parent–child link found. Skipping auto-activation.</comment>'
			);
		}
	}

	/**
	 * Read "Template: <parent>" from /wp-content/themes/<slug>/style.css.
	 *
	 * Returns parent slug or null if not found.
	 * We do not handle "parent not installed" ourselves; WP-CLI may fail if so.
	 */
	protected function detect_parent_template( string $slug ): ?string {
		$cat_output = $this->docker->run_inside_docker(
			$this->env_info,
			[
				'bash',
				'-c',
				sprintf(
					'cat /var/www/html/wp-content/themes/%s/style.css || echo "NO_STYLECSS"',
					escapeshellarg( $slug )
				),
			]
		);

		if ( stripos( $cat_output, 'NO_STYLECSS' ) !== false ) {
			return null;
		}

		if ( preg_match( '/Template:\s*(\S+)/i', $cat_output, $matches ) ) {
			return trim( strtolower( $matches[1] ) );
		}

		return null;
	}
}
