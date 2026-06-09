#!/usr/bin/env php
<?php

/**
 * compare_snapshots.php
 *
 * 1) Moves to your Git repository root (so "git show HEAD:<file>" works properly).
 * 2) Runs "git status --porcelain" to find changed files containing "__snapshots__".
 * 3) For each changed file:
 *    - "before": last committed content (git show HEAD:<file>)
 *    - "after":  current uncommitted content on disk (if still exists)
 * 4) If there's a difference, calls Ollama to summarize focusing on test_tags/php_extension changes.
 * 5) Prints a final Markdown summary of differences.
 *
 * Usage:
 *   php compare_snapshots.php
 */

// -----------------------------------------------------------------------------
// 1. CONFIG
// -----------------------------------------------------------------------------

// Ollama endpoint/model
$ollamaEndpoint = 'http://localhost:11434/api/generate';
$ollamaModel    = 'qwen2.5-coder:14b';

// We'll ask Ollama to focus on test_tags & php_extension
$systemPrompt = <<<SYSPROMPT
You are an AI that compares two JSON objects:
- "before" (from the last commit)
- "after" (current uncommitted content).
Highlight changes in "test_tags", "php_extension", and any other notable differences. 
Return a concise **Markdown** summary.
SYSPROMPT;

// -----------------------------------------------------------------------------
// 2. HELPER FUNCTIONS
// -----------------------------------------------------------------------------

/**
 * Run a shell command, return the combined stdout as a string (empty on error).
 */
function runShellCommand( string $cmd ): string {
	$output    = [];
	$returnVar = 0;
	exec( $cmd . ' 2>/dev/null', $output, $returnVar );

	return ( $returnVar === 0 ) ? implode( "\n", $output ) : '';
}

/**
 * Call Ollama with a system message and user prompt, return raw text from "response".
 */
function callOllama( string $endpoint, string $model, string $system, string $user ): string {
	$payload = [
		'model'  => $model,
		'system' => $system,
		'prompt' => $user,
		'stream' => false,
	];

	$ch = curl_init( $endpoint );
	curl_setopt( $ch, CURLOPT_POST, true );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $payload ) );
	curl_setopt( $ch, CURLOPT_HTTPHEADER, [
		'Content-Type: application/json',
		'Accept: application/json'
	] );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

	$response = curl_exec( $ch );
	if ( $response === false ) {
		$err = curl_error( $ch );

		return "**Error calling Ollama**: $err";
	}

	$decoded = json_decode( $response, true );
	if ( ! is_array( $decoded ) ) {
		return "**Invalid Ollama response** (not JSON):\n$response";
	}

	return $decoded['response'] ?? "**No 'response' field in Ollama output**";
}

// -----------------------------------------------------------------------------
// 3. MAIN
// -----------------------------------------------------------------------------

// 3.1) Move to the repo root so git commands reference correct file paths
$repoRoot = runShellCommand( 'git rev-parse --show-toplevel' );
if ( ! $repoRoot ) {
	echo "Error: Could not find repo root.\n";
	exit( 1 );
}
chdir( $repoRoot );

// 3.2) Identify changed files in __snapshots__ from "git status --porcelain"
$statusOutput = runShellCommand( "git status --porcelain" );
if ( trim( $statusOutput ) === '' ) {
	echo "No changes found in the repository.\n";
	exit( 0 );
}

// Filter lines that mention "__snapshots__"
$lines        = explode( "\n", $statusOutput );
$changedFiles = [];
foreach ( $lines as $line ) {
	$line = trim( $line );
	if ( $line === '' ) {
		continue;
	}
	// Typical format: "M  path/to/file" or "?? path/to/new/file"
	// or "D  path/to/deleted/file"
	if ( strpos( $line, '__snapshots__' ) !== false ) {
		// The path starts at index 3 of the line
		$filePath = substr( $line, 2 );
		$filePath = trim( $filePath );
		// We only care if the file still shows in 'git show HEAD:...' references
		// or if it's new or deleted. We'll store them anyway.
		$changedFiles[] = $filePath;
	}
}

if ( empty( $changedFiles ) ) {
	echo "No changed files found under '__snapshots__' directory.\n";
	exit( 0 );
}

// 3.3) Compare each changed snapshot file
$results = [];

foreach ( $changedFiles as $file ) {
	// "before" = last commit
	$beforeContent = runShellCommand( "git show HEAD:$file" );
	// "after" = what's on disk, if it exists
	$afterContent = '';
	if ( is_file( $file ) ) {
		$afterContent = file_get_contents( $file );
	}

	// Check if new
	if ( trim( $beforeContent ) === '' && file_exists( $file ) ) {
		// Means file doesn't exist in HEAD
		$results[] = [
			'file'    => $file,
			'summary' => "**New file** (no version in HEAD)."
		];
		continue;
	}

	// Check if removed
	if ( trim( $beforeContent ) !== '' && ! file_exists( $file ) ) {
		$results[] = [
			'file'    => $file,
			'summary' => "**File removed** (was in HEAD, now gone)."
		];
		continue;
	}

	// If no difference, skip
	if ( $beforeContent === $afterContent ) {
		continue;
	}

	// Otherwise, we have real differences
	$userPrompt = <<<UPROMPT
Here is the "before" JSON (from last commit):
```
$beforeContent
```

Here is the "after" JSON (current uncommitted content):
```
$afterContent
```

Focus on "test_tags" and "php_extension" changes, plus any other major differences.
Return a concise **Markdown** summary.
UPROMPT;

	$ollamaResponse = callOllama( $ollamaEndpoint, $ollamaModel, $systemPrompt, $userPrompt );

	$results[] = [
		'file'    => $file,
		'summary' => $ollamaResponse
	];
}

// 3.4) Print final results
if ( empty( $results ) ) {
	echo "No meaningful differences found in '__snapshots__' files.\n";
	exit( 0 );
}

echo "# Comparison Summary (Git HEAD vs. Working Copy in __snapshots__)\n\n";
foreach ( $results as $r ) {
	echo "## File: `{$r['file']}`\n\n";
	echo "{$r['summary']}\n\n---\n\n";
}
echo "Done.\n";