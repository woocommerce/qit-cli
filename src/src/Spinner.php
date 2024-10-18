<?php

namespace QIT_CLI;

use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;

/**
 * Spinner class that provides a CLI spinner with color support.
 */
class Spinner {
	public const CHARS = [ '⠏', '⠛', '⠹', '⢸', '⣰', '⣤', '⣆', '⡇' ];

	private const COLORS = [
		196,
		196,
		202,
		202,
		208,
		208,
		214,
		214,
		220,
		220,
		226,
		226,
		190,
		190,
		154,
		154,
		118,
		118,
		82,
		82,
		46,
		46,
		47,
		47,
		48,
		48,
		49,
		49,
		50,
		50,
		51,
		51,
		45,
		45,
		39,
		39,
		33,
		33,
		27,
		27,
		56,
		56,
		57,
		57,
		93,
		93,
		129,
		129,
		165,
		165,
		201,
		201,
		200,
		200,
		199,
		199,
		198,
		198,
		197,
		197,
	];

	public const NO_COLOR = 0;
	public const COLOR_16 = 16;
	public const COLOR_256 = 256;

	public const ALLOWED = [
		self::NO_COLOR,
		self::COLOR_16,
		self::COLOR_256,
	];

	private $currentCharIdx = 0;
	private $currentColorIdx = 0;
	private $colorCount;
	private $progressBar;
	private $colorLevel;
	private $section;
	private $output;
	private $indentLength;

	/**
	 * Constructor for the Spinner class.
	 *
	 * @param OutputInterface $output
	 * @param int $indent
	 * @param int $colorLevel
	 */
	public function __construct( OutputInterface $output, $indent = 0, $colorLevel = self::COLOR_256 ) {
		$this->output       = $output;
		$this->indentLength = $indent;
		$indentString       = str_repeat( ' ', $indent );

		if ( ! $this->spinnerIsSupported() ) {
			return;
		}
		$this->section    = $output->section();
		$this->colorLevel = $colorLevel;
		$this->colorCount = count( self::COLORS );

		// Create progress bar.
		$this->progressBar = new ProgressBar( $this->section );
		$this->progressBar->setBarCharacter( '<info>✔</info>' );
		$this->progressBar->setProgressCharacter( '⌛' );
		$this->progressBar->setEmptyBarCharacter( '⌛' );
		$this->progressBar->setFormat( $indentString . "%bar% %message%\n%detail%" );
		$this->progressBar->setBarWidth( 1 );
		$this->progressBar->setMessage( '', 'detail' );
		$this->progressBar->setOverwrite( $output->getVerbosity() < OutputInterface::VERBOSITY_VERBOSE );
	}

	/**
	 * Starts the spinner.
	 */
	public function start() {
		if ( ! $this->spinnerIsSupported() ) {
			return;
		}
		$this->progressBar->start();
	}

	/**
	 * Advances the spinner to the next state.
	 */
	public function advance() {
		if ( ! $this->spinnerIsSupported() || $this->progressBar->getProgressPercent() === 1.0 ) {
			return;
		}

		++ $this->currentCharIdx;
		++ $this->currentColorIdx;
		$char = $this->getSpinnerCharacter();
		$this->progressBar->setProgressCharacter( $char );
		$this->progressBar->advance();
	}

	/**
	 * Retrieves the current spinner character with appropriate color.
	 *
	 * @return string|null
	 */
	private function getSpinnerCharacter() {
		if ( $this->currentColorIdx === $this->colorCount ) {
			$this->currentColorIdx = 0;
		}
		$char  = self::CHARS[ $this->currentCharIdx % 8 ];
		$color = self::COLORS[ $this->currentColorIdx ];

		if ( self::COLOR_256 === $this->colorLevel ) {
			return "\033[38;5;{$color}m{$char}\033[0m";
		}
		if ( self::COLOR_16 === $this->colorLevel ) {
			return "\033[96m{$char}\033[0m";
		}

		return null;
	}

	/**
	 * Sets a message to display next to the spinner.
	 *
	 * @param string $message
	 * @param string $name
	 */
	public function setMessage( $message, $name = 'message' ) {
		if ( ! $this->spinnerIsSupported() ) {
			return;
		}
		if ( $name === 'detail' ) {
			$terminal_width = ( new Terminal() )->getWidth();
			$message_length = Helper::length( $message ) + ( $this->indentLength * 2 );
			if ( $message_length > $terminal_width ) {
				$suffix          = '...';
				$new_message_len = ( $terminal_width - ( $this->indentLength * 2 ) - strlen( $suffix ) );
				$message         = Helper::substr( $message, 0, $new_message_len );
				$message         .= $suffix;
			}
		}
		$this->progressBar->setMessage( $message, $name );
	}

	/**
	 * Finishes the spinner display.
	 */
	public function finish() {
		if ( ! $this->spinnerIsSupported() ) {
			return;
		}
		$this->progressBar->finish();
		// Clear the %detail% line.
		$this->section->clear( 1 );
	}

	/**
	 * Indicates that the spinner operation has failed.
	 */
	public function fail() {
		if ( ! $this->spinnerIsSupported() ) {
			return;
		}
		$this->progressBar->finish();
		// Clear the %detail% line.
		$this->section->clear( 1 );
	}

	/**
	 * Returns spinner refresh interval.
	 *
	 * @return float
	 */
	public function interval() {
		return 0.1;
	}

	/**
	 * Checks if the spinner is supported in the current environment.
	 *
	 * @return bool
	 */
	private function spinnerIsSupported() {
		return $this->output instanceof ConsoleOutput;
	}

	/**
	 * Retrieves the underlying ProgressBar instance.
	 *
	 * @return ProgressBar
	 */
	public function getProgressBar() {
		return $this->progressBar;
	}
}