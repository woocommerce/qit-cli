<?php

class Logger {
	private $logfile;

	public function __construct($logfile) {
		$this->logfile = $logfile;
		// Clear the log file if it exists
		if (file_exists($this->logfile)) {
			unlink($this->logfile);
		}
	}

	public function log($message) {
		$timestamp = date('[Y-m-d H:i:s]');
		file_put_contents($this->logfile, "$timestamp $message\n", FILE_APPEND);
	}
}
