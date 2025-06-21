<?php

namespace QIT_CLI\Logging;

/**
 * Logger class for QIT CLI.
 * 
 * Provides logging functionality that writes to a file in the system's temporary directory.
 */
class Logger {
    // Log levels
    const DEBUG    = 'debug';
    const INFO     = 'info';
    const WARNING  = 'warning';
    const ERROR    = 'error';
    const CRITICAL = 'critical';
    
    // Log file path
    private string $log_file;
    
    // Log level threshold
    private string $log_level;
    
    // Log level priorities (lower number = higher priority)
    private array $log_level_priorities = [
        self::DEBUG    => 0,
        self::INFO     => 1,
        self::WARNING  => 2,
        self::ERROR    => 3,
        self::CRITICAL => 4,
    ];
    
    /**
     * Constructor.
     * 
     * @param string|null $log_file  Optional. The log file path. If null, a default path in the system's temporary directory will be used.
     * @param string      $log_level Optional. The minimum log level to record. Default is 'info'.
     */
    public function __construct(?string $log_file = null, string $log_level = self::INFO) {
        // If no log file is specified, create one in the system's temporary directory
        if ($log_file === null) {
            $log_file = sys_get_temp_dir() . '/qit-node.log';
        }
        
        $this->log_file = $log_file;
        $this->log_level = $log_level;
        
        // Create log file if it doesn't exist and write header
        if (!file_exists($log_file)) {
            $this->write_to_log("=== QIT Node Log Started at " . date('Y-m-d H:i:s') . " ===\n");
        }
    }
    
    /**
     * Get the log file path.
     * 
     * @return string The log file path.
     */
    public function get_log_file(): string {
        return $this->log_file;
    }
    
    /**
     * Log a debug message.
     * 
     * @param string $message The message to log.
     * @param array  $context Optional. Additional context data to include in the log.
     */
    public function debug(string $message, array $context = []): void {
        $this->log(self::DEBUG, $message, $context);
    }
    
    /**
     * Log an info message.
     * 
     * @param string $message The message to log.
     * @param array  $context Optional. Additional context data to include in the log.
     */
    public function info(string $message, array $context = []): void {
        $this->log(self::INFO, $message, $context);
    }
    
    /**
     * Log a warning message.
     * 
     * @param string $message The message to log.
     * @param array  $context Optional. Additional context data to include in the log.
     */
    public function warning(string $message, array $context = []): void {
        $this->log(self::WARNING, $message, $context);
    }
    
    /**
     * Log an error message.
     * 
     * @param string $message The message to log.
     * @param array  $context Optional. Additional context data to include in the log.
     */
    public function error(string $message, array $context = []): void {
        $this->log(self::ERROR, $message, $context);
    }
    
    /**
     * Log a critical message.
     * 
     * @param string $message The message to log.
     * @param array  $context Optional. Additional context data to include in the log.
     */
    public function critical(string $message, array $context = []): void {
        $this->log(self::CRITICAL, $message, $context);
    }
    
    /**
     * Log a message with the specified level.
     * 
     * @param string $level   The log level.
     * @param string $message The message to log.
     * @param array  $context Optional. Additional context data to include in the log.
     */
    public function log(string $level, string $message, array $context = []): void {
        // Check if this log level should be recorded
        if (!$this->should_log($level)) {
            return;
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $formatted_message = "[$timestamp] [$level] $message";
        
        // Add context if available
        if (!empty($context)) {
            $formatted_message .= " " . json_encode($context, JSON_UNESCAPED_SLASHES);
        }
        
        // Write to log file
        $this->write_to_log($formatted_message . PHP_EOL);
        
        // Also write to error_log for system logging
        error_log("[QIT Node] $formatted_message");
    }
    
    /**
     * Check if the given log level should be recorded based on the current log level threshold.
     * 
     * @param string $level The log level to check.
     * @return bool True if the log level should be recorded, false otherwise.
     */
    private function should_log(string $level): bool {
        // If the level doesn't exist in our priorities, default to logging it
        if (!isset($this->log_level_priorities[$level])) {
            return true;
        }
        
        // Log if the level priority is >= the threshold priority
        // (Remember: lower number = higher priority)
        return $this->log_level_priorities[$level] >= $this->log_level_priorities[$this->log_level];
    }
    
    /**
     * Write a message to the log file.
     * 
     * @param string $message The message to write.
     */
    private function write_to_log(string $message): void {
        file_put_contents($this->log_file, $message, FILE_APPEND);
    }
}