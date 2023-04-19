<?php

namespace QIT_CLI;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This package is ported from https://github.com/icanhazstring/symfony-console-spinner
 * All credits to the maintainers of that repository.
 * Available in Packagist under the name `icanhazstring/symfony-console-spinner`
 */
class SpinnerProgress {
	private const CHARS = [ '⠏', '⠛', '⠹', '⢸', '⣰', '⣤', '⣆', '⡇' ];

	/**
	 * @var ProgressBar
	 */
	private $progress_bar;
	/**
	 * @var int
	 */
	private $step;

	public function __construct( OutputInterface $output, int $max = 0 ) {
		if ( $output instanceof ConsoleOutputInterface ) {
			$progress_bar_output = $output->section();
		} else {
			$progress_bar_output = $output;
		}

		$this->progress_bar = new ProgressBar( $progress_bar_output, $max );
		$this->progress_bar->setBarCharacter( '✔' );
		$this->progress_bar->setFormat( '%bar%  %message%' );
		$this->progress_bar->setBarWidth( 1 );
		$this->progress_bar->setRedrawFrequency( 31 );

		$this->step = 0;
	}

	public function advance( int $step = 1 ): void {
		$this->step += $step;
		$this->progress_bar->setProgressCharacter( self::CHARS[ $this->step % 8 ] );
		$this->progress_bar->advance( $step );
	}

	public function set_message( string $message ): void {
		$this->progress_bar->setMessage( $message, 'message' );
	}

	public function start(): void {
		for ( $i = 0; $i < 100; $i ++ ) {
			usleep( 1000 );
			$this->advance();
		}
	}

	public function finish(): void {
		$this->progress_bar->finish();
	}

	public function get_progress_bar(): ProgressBar {
		return $this->progress_bar;
	}
}
