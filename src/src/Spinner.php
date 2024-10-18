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

	public const NO_COLOR  = 0;
	public const COLOR_16  = 16;
	public const COLOR_256 = 256;

	public const ALLOWED = [
		self::NO_COLOR,
		self::COLOR_16,
		self::COLOR_256,
	];

	/** @var int */
	private $current_char_idx = 0;

	/** @var int */
	private $current_color_idx = 0;

	/** @var int|null */
	private $color_count;

	/** @var ProgressBar */
	private $progress_bar;

	/** @var int */
	private $color_level;

	/** @var ConsoleSectionOutput */
	private $section;

	/** @var OutputInterface */
	private $output;

	/** @var int */
	private $indent_length;

	/**
	 * Constructor for the Spinner class.
	 *
	 * @param OutputInterface $output
	 * @param int             $indent
	 * @param int             $color_level
	 */
	public function __construct( OutputInterface $output, $indent = 0, $color_level = self::COLOR_256 ) {
		$this->output        = $output;
		$this->indent_length = $indent;
		$indent_string       = str_repeat( ' ', $indent );

		if ( ! $this->spinner_is_supported() ) {
			return;
		}

		if ( ! method_exists( $output, 'section' ) ) {
			return;
		}

		$this->section     = $output->section(); // @phan-suppress-current-line PhanUndeclaredMethod
		$this->color_level = $color_level;
		$this->color_count = count( self::COLORS );

		// Create progress bar.
		$this->progress_bar = new ProgressBar( $this->section );
		$this->progress_bar->setBarCharacter( '<info>✔</info>' );
		$this->progress_bar->setProgressCharacter( '⌛' );
		$this->progress_bar->setEmptyBarCharacter( '⌛' );
		$this->progress_bar->setFormat( $indent_string . "%bar% %message%\n%detail%" );
		$this->progress_bar->setBarWidth( 1 );
		$this->progress_bar->setMessage( '', 'detail' );
		$this->progress_bar->setOverwrite( $output->getVerbosity() < OutputInterface::VERBOSITY_VERBOSE );
	}

	/**
	 * Starts the spinner.
	 */
	public function start(): void {
		if ( ! $this->spinner_is_supported() ) {
			return;
		}
		$this->progress_bar->start();
	}

	/**
	 * Advances the spinner to the next state.
	 */
	public function advance(): void {
		if ( ! $this->spinner_is_supported() || $this->progress_bar->getProgressPercent() === 1.0 ) {
			return;
		}

		++$this->current_char_idx;
		++$this->current_color_idx;
		$char = $this->get_spinner_character();
		$this->progress_bar->setProgressCharacter( $char );
		$this->progress_bar->advance();
	}

	/**
	 * Retrieves the current spinner character with appropriate color.
	 *
	 * @return string|null
	 */
	private function get_spinner_character(): ?string {
		if ( $this->current_color_idx === $this->color_count ) {
			$this->current_color_idx = 0;
		}
		$char  = self::CHARS[ $this->current_char_idx % 8 ];
		$color = self::COLORS[ $this->current_color_idx ];

		if ( self::COLOR_256 === $this->color_level ) {
			return "\033[38;5;{$color}m{$char}\033[0m";
		}
		if ( self::COLOR_16 === $this->color_level ) {
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
	public function set_message( $message, $name = 'message' ): void {
		if ( ! $this->spinner_is_supported() ) {
			return;
		}
		if ( $name === 'detail' ) {
			$terminal_width = ( new Terminal() )->getWidth();
			$message_length = Helper::length( $message ) + ( $this->indent_length * 2 );
			if ( $message_length > $terminal_width ) {
				$suffix          = '...';
				$new_message_len = ( $terminal_width - ( $this->indent_length * 2 ) - strlen( $suffix ) );
				$message         = Helper::substr( $message, 0, $new_message_len );
				$message        .= $suffix;
			}
		}
		$this->progress_bar->setMessage( $message, $name );
	}

	/**
	 * Finishes the spinner display.
	 */
	public function finish(): void {
		if ( ! $this->spinner_is_supported() ) {
			return;
		}
		$this->progress_bar->finish();
		// Clear the %detail% line.
		$this->section->clear( 1 );
	}

	/**
	 * Indicates that the spinner operation has failed.
	 */
	public function fail(): void {
		if ( ! $this->spinner_is_supported() ) {
			return;
		}
		$this->progress_bar->finish();
		// Clear the %detail% line.
		$this->section->clear( 1 );
	}

	/**
	 * Returns spinner refresh interval.
	 *
	 * @return float
	 */
	public function interval(): float {
		return 0.1;
	}

	/**
	 * Checks if the spinner is supported in the current environment.
	 *
	 * @return bool
	 */
	private function spinner_is_supported(): bool {
		return $this->output instanceof ConsoleOutput;
	}

	/**
	 * Retrieves the underlying ProgressBar instance.
	 *
	 * @return ProgressBar
	 */
	public function get_progress_bar(): ProgressBar {
		return $this->progress_bar;
	}
}
