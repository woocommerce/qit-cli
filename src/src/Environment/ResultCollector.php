<?php
namespace QIT_CLI\Environment;

use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\PreCommand\Objects\TestPackageManifest;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Style\SymfonyStyle;
use RuntimeException;

/**
 * Handles result collection and merging for CTRF and Allure reports
 */
class ResultCollector {
    private NodeDependencyManager $node_deps;
    private Docker $docker;
    
    public function __construct(Docker $docker) {
        $this->node_deps = new NodeDependencyManager();
        $this->docker = $docker;
    }
    
    /**
     * Collect artifacts from a test package after it finishes running
     */
    public function collect(E2EEnvInfo $env_info, string $slug, TestPackageManifest $manifest, string $artifactsDir, string $phase = 'run'): void {
        $container_pkg_root = "/qit/packages/" . basename($slug);
        $test_results = $manifest->getTestResults();
        
        // 1) CTRF collection
        $ctrfRel = $test_results['ctrf-json'] ?? null;
        if ($ctrfRel) {
            $container_ctrf_path = $container_pkg_root . '/' . ltrim($ctrfRel, './');
            $host_ctrf_path = $artifactsDir . '/ctrf/' . basename($slug) . '.json';
            
            // Ensure ctrf directory exists
            $ctrf_dir = dirname($host_ctrf_path);
            if (!is_dir($ctrf_dir)) {
                mkdir($ctrf_dir, 0755, true);
            }
            
            try {
                // Tag CTRF before copying
                $this->tagCtrfInContainer($env_info, $container_ctrf_path, $slug, $manifest, $phase);
                
                // Copy from container to host
                $this->docker->copy_from_docker($env_info, $container_ctrf_path, $host_ctrf_path);
            } catch (RuntimeException $e) {
                // CTRF file might not exist if tests didn't run - that's okay
            }
        }
        
        // 2) Allure collection (optional)
        $allureRel = $test_results['allure-dir'] ?? null;
        if ($allureRel) {
            $container_allure_path = $container_pkg_root . '/' . trim($allureRel, '/');
            $host_allure_path = $artifactsDir . '/allure/' . basename($slug);
            
            // Ensure allure directory exists
            if (!is_dir(dirname($host_allure_path))) {
                mkdir(dirname($host_allure_path), 0755, true);
            }
            
            try {
                // Copy recursively from container to host
                $this->docker->copy_from_docker($env_info, $container_allure_path, $host_allure_path);
            } catch (RuntimeException $e) {
                // Allure results might not exist if tests didn't run - that's okay
            }
        }
    }
    
    /**
     * Tag CTRF file inside the container with package metadata
     */
    private function tagCtrfInContainer(E2EEnvInfo $env_info, string $container_ctrf_path, string $package_id, TestPackageManifest $manifest, string $phase): void {
        $tag_script = sprintf(
            'if [ -f "%s" ]; then
                php -r "
                    \$data = json_decode(file_get_contents(\"%s\"), true);
                    if (is_array(\$data) && !empty(\$data[\"results\"][\"tests\"]) && is_array(\$data[\"results\"][\"tests\"])) {
                        foreach (\$data[\"results\"][\"tests\"] as &\$test) {
                            if (!isset(\$test[\"extra\"])) {
                                \$test[\"extra\"] = [];
                            }
                            \$test[\"extra\"][\"packageSlug\"] = \"%s\";
                            \$test[\"extra\"][\"phase\"] = \"%s\";
                            \$test[\"extra\"][\"testType\"] = \"%s\";
                            \$test[\"extra\"][\"namespace\"] = \"%s\";
                        }
                        file_put_contents(\"%s\", json_encode(\$data, JSON_PRETTY_PRINT));
                    }
                ";
            fi',
            $container_ctrf_path,
            $container_ctrf_path,
            $package_id,
            $phase,
            $manifest->getTestType(),
            $manifest->getNamespace(),
            $container_ctrf_path
        );
        
        try {
            $this->docker->run_inside_docker(
                $env_info,
                ['/bin/bash', '-c', $tag_script],
                [],
                null,
                30,
                'php',
                false
            );
        } catch (RuntimeException $e) {
            // Tagging failed - continue anyway
        }
    }
    
    /**
     * Merge all collected artifacts into final reports
     */
    public function mergeAllArtifacts(string $artifactsDir, SymfonyStyle $io): void {
        $this->mergeCtrf($artifactsDir, $io);
        $this->mergeAllure($artifactsDir, $io);
    }
    
    private function mergeCtrf(string $artifactsDir, SymfonyStyle $io): void {
        $ctrfDir = $artifactsDir . '/ctrf';
        
        // Skip if no CTRF files
        if (!is_dir($ctrfDir) || empty(glob($ctrfDir . '/*.json'))) {
            return;
        }
        
        // Ensure ctrf-cli is available
        $bin_dir = $this->node_deps->ensurePackages(['ctrf-cli'], $io);
        $ctrf_bin = $bin_dir . '/ctrf';
        
        $io->text('Merging CTRF reports...');
        
        $proc = new Process([$ctrf_bin, 'merge', $ctrfDir]);
        $proc->setTimeout(300);
        $proc->run(function ($type, $buf) use ($io) { 
            if (!$io->isQuiet()) {
                $io->write($buf); 
            }
        });
        
        if (!$proc->isSuccessful()) {
            throw new RuntimeException('CTRF merge failed: ' . $proc->getErrorOutput());
        }
        
        // Move merged report to final location
        $final_dir = $artifactsDir . '/final/ctrf';
        if (!is_dir($final_dir)) {
            mkdir($final_dir, 0755, true);
        }
        
        if (file_exists($ctrfDir . '/ctrf-report.json')) {
            rename($ctrfDir . '/ctrf-report.json', $final_dir . '/ctrf-report.json');
        }
    }
    
    private function mergeAllure(string $artifactsDir, SymfonyStyle $io): void {
        $allureDir = $artifactsDir . '/allure';
        
        // Skip if no Allure directories
        $paths = glob($allureDir . '/*');
        if (empty($paths)) {
            return;
        }
        
        // Ensure allure-commandline is available
        $bin_dir = $this->node_deps->ensurePackages(['allure-commandline'], $io);
        $allure_bin = $bin_dir . '/allure';
        
        $io->text('Generating Allure HTML report...');
        
        $outDir = $artifactsDir . '/final/allure-html';
        $args = array_merge(['generate', '--clean', '-o', $outDir], $paths);
        
        $proc = new Process(array_merge([$allure_bin], $args));
        $proc->setTimeout(300);
        $proc->run(function ($type, $buf) use ($io) { 
            if (!$io->isQuiet()) {
                $io->write($buf); 
            }
        });
        
        if (!$proc->isSuccessful()) {
            throw new RuntimeException('Allure generate failed: ' . $proc->getErrorOutput());
        }
    }
    
    /**
     * Map container result paths to host artifact directories
     */
    public function map_container_to_host_paths(TestPackageManifest $manifest, string $package_id, string $host_artifacts_dir): array {
        $mappings = [];
        $results = $manifest->getTestResults();
        
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