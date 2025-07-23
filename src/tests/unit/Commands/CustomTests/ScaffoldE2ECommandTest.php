<?php

namespace QIT_CLI\Tests\Unit\Commands\CustomTests;

use PHPUnit\Framework\TestCase;
use QIT_CLI\Commands\CustomTests\ScaffoldE2ECommand;

class ScaffoldE2ECommandTest extends TestCase {
    public function test_scaffold_command_has_required_options() {
        $command = new ScaffoldE2ECommand();
        
        $definition = $command->getDefinition();
        
        // Check that vendor, package, framework, and only-manifest options exist
        $this->assertTrue($definition->hasOption('vendor'));
        $this->assertTrue($definition->hasOption('package'));
        $this->assertTrue($definition->hasOption('framework'));
        $this->assertTrue($definition->hasOption('only-manifest'));
        
        // Check that vendor, package, and framework require values when provided
        $vendorOption = $definition->getOption('vendor');
        $packageOption = $definition->getOption('package');
        $frameworkOption = $definition->getOption('framework');
        
        $this->assertTrue($vendorOption->isValueRequired());
        $this->assertTrue($packageOption->isValueRequired());
        $this->assertTrue($frameworkOption->isValueRequired());
        
        // Check that only-manifest is a flag (no value)
        $onlyManifestOption = $definition->getOption('only-manifest');
        $this->assertFalse($onlyManifestOption->acceptValue());
    }
    
    public function test_scaffold_command_has_target_dir_argument() {
        $command = new ScaffoldE2ECommand();
        
        $definition = $command->getDefinition();
        
        // Check that target_dir argument exists and is required
        $this->assertTrue($definition->hasArgument('target_dir'));
        $targetDirArgument = $definition->getArgument('target_dir');
        $this->assertTrue($targetDirArgument->isRequired());
    }
    
    public function test_scaffold_command_has_correct_description() {
        $command = new ScaffoldE2ECommand();
        
        $this->assertStringContainsString('Scaffold an E2E test package (manifest-first approach)', $command->getDescription());
    }
    
    public function test_scaffold_command_removed_old_options() {
        $command = new ScaffoldE2ECommand();
        
        $definition = $command->getDefinition();
        
        // Check that old options are removed
        $this->assertFalse($definition->hasOption('with-playwright'));
        $this->assertFalse($definition->hasOption('language'));
        $this->assertFalse($definition->hasOption('download-browsers'));
        $this->assertFalse($definition->hasOption('include-examples'));
        $this->assertFalse($definition->hasOption('force'));
        $this->assertFalse($definition->hasOption('with-shared'));
        $this->assertFalse($definition->hasOption('with-teardown'));
    }
}