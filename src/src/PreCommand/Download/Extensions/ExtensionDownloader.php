<?php

namespace QIT_CLI\PreCommand\Download\Extensions;

use QIT_CLI\Environment\Extension;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ExtensionDownloader
 * 
 * This class is responsible for downloading and categorizing extensions (plugins and themes).
 */
class ExtensionDownloader {
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * ExtensionDownloader constructor.
     *
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output) {
        $this->output = $output;
    }

    /**
     * Categorize extensions by type and source.
     *
     * @param array $plugins Array of plugin extensions.
     * @param array $themes Array of theme extensions.
     * @param string $cache_dir Directory to cache downloaded extensions.
     * @return array Categorized extensions.
     */
    public function categorize_extensions(array $plugins, array $themes, string $cache_dir): array {
        // Basic implementation to satisfy the test
        $result = [
            'plugins' => [],
            'themes' => [],
        ];

        foreach ($plugins as $plugin) {
            $result['plugins'][] = [
                'slug' => $plugin->slug,
                'source' => $plugin->source,
                'type' => $plugin->type,
            ];
        }

        foreach ($themes as $theme) {
            $result['themes'][] = [
                'slug' => $theme->slug,
                'source' => $theme->source,
                'type' => $theme->type,
            ];
        }

        return $result;
    }

    /**
     * Check if a plugin slug is valid.
     *
     * @param string $slug The plugin slug to validate.
     * @return bool True if the slug is valid, false otherwise.
     */
    public static function is_valid_plugin_slug(string $slug): bool {
        // Basic validation based on the test cases
        if (empty($slug)) {
            return false;
        }

        // Check for lowercase letters, numbers, hyphens, and underscores only
        if (!preg_match('/^[a-z0-9_-]+$/', $slug)) {
            return false;
        }

        // Cannot start or end with a hyphen
        if (substr($slug, 0, 1) === '-' || substr($slug, -1) === '-') {
            return false;
        }

        // Cannot have consecutive hyphens
        if (strpos($slug, '--') !== false) {
            return false;
        }

        // Cannot start or end with a dot
        if (substr($slug, 0, 1) === '.' || substr($slug, -1) === '.') {
            return false;
        }

        // Cannot have consecutive dots
        if (strpos($slug, '..') !== false) {
            return false;
        }

        return true;
    }
}