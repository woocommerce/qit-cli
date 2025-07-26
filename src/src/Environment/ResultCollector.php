<?php
namespace QIT_CLI\Environment;

use QIT_CLI\PreCommand\Objects\TestPackageManifest;

/**
 * Handles result collection and mapping from container to host paths
 */
class ResultCollector {
    
    /**
     * Map container result paths to host artifact directories
     */
    public function map_container_to_host_paths(TestPackageManifest $manifest, string $package_id, string $host_artifacts_dir): array {
        $mappings = [];
        $results = $manifest->test['results'] ?? [];
        
        foreach ($results as $type => $container_path) {
            // Handle relative paths
            if (strpos($container_path, './') === 0) {
                $container_path = '/qit/packages/' . basename($package_id) . '/' . substr($container_path, 2);
            }
            
            $host_path = rtrim($host_artifacts_dir, '/') . '/' . $package_id . '/' . $type;
            
            $mappings[] = [
                'container_path' => $container_path,
                'host_path' => $host_path,
                'type' => $type
            ];
        }
        
        return $mappings;
    }
    
    /**
     * Tag CTRF with package metadata instead of plugin slug
     */
    public function tag_ctrf_with_package_metadata(string $package_id, TestPackageManifest $manifest): array {
        return [
            'packageSlug' => $package_id,
            'testType' => $manifest->getTestType(),
            'namespace' => $manifest->getNamespace(),
        ];
    }
}