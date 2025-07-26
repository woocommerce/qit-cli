<?php
namespace QIT_CLI\Environment;

use QIT_CLI\Config;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Style\SymfonyStyle;
use RuntimeException;

class NodeDependencyManager {
    private string $cache_dir;
    
    public function __construct() {
        // Use QIT config directory + node-deps subdirectory
        $this->cache_dir = Config::get_qit_dir() . 'node-deps';
        if (!is_dir($this->cache_dir)) {
            mkdir($this->cache_dir, 0755, true);
        }
    }
    
    /**
     * Ensure required npm packages are available
     * @param array $packages ['ctrf-cli', 'allure-commandline']
     * @return string Path to node_modules/.bin directory
     */
    public function ensurePackages(array $packages, SymfonyStyle $io): string {
        $bin_dir = $this->cache_dir . '/node_modules/.bin';
        
        // Check if all packages are already installed
        if ($this->arePackagesInstalled($packages)) {
            return $bin_dir;
        }
        
        $io->text('Installing required Node.js dependencies...');
        $this->installPackages($packages, $io);
        
        return $bin_dir;
    }
    
    private function arePackagesInstalled(array $packages): bool {
        $bin_dir = $this->cache_dir . '/node_modules/.bin';
        
        foreach ($packages as $package) {
            // Extract binary name from package name
            $binary = $this->getBinaryName($package);
            if (!file_exists($bin_dir . '/' . $binary)) {
                return false;
            }
        }
        
        return true;
    }
    
    private function installPackages(array $packages, SymfonyStyle $io): void {
        // Initialize package.json if it doesn't exist
        $package_json = $this->cache_dir . '/package.json';
        if (!file_exists($package_json)) {
            file_put_contents($package_json, json_encode([
                'name' => 'qit-node-deps',
                'private' => true,
                'description' => 'QIT Node.js dependencies cache'
            ], JSON_PRETTY_PRINT));
        }
        
        // Install packages
        $install_cmd = array_merge(['npm', 'install', '--no-save'], $packages);
        $process = new Process($install_cmd, $this->cache_dir);
        $process->setTimeout(300);
        
        $process->run(function ($type, $buffer) use ($io) {
            if (!$io->isQuiet()) {
                $io->write($buffer);
            }
        });
        
        if (!$process->isSuccessful()) {
            throw new RuntimeException(
                'Failed to install Node.js dependencies: ' . $process->getErrorOutput()
            );
        }
    }
    
    private function getBinaryName(string $package): string {
        // Map package names to their binary names
        $binary_map = [
            'ctrf-cli' => 'ctrf',
            'allure-commandline' => 'allure',
        ];
        
        return $binary_map[$package] ?? $package;
    }
    
    /**
     * Get the full path to a specific binary
     */
    public function getBinaryPath(string $package): string {
        $binary = $this->getBinaryName($package);
        return $this->cache_dir . '/node_modules/.bin/' . $binary;
    }
}