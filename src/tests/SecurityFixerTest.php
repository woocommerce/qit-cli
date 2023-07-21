<?php

use QIT_CLI\App;
use QIT_CLI\Fixer\Exceptions\SecurityFixerException;
use QIT_CLI\Fixer\SecurityFixer;
use QIT_CLI\IO\Output;
use QIT_CLI_Tests\QITTestCase;
use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Filesystem\Filesystem;
use function QIT_CLI_Tests\data\calculate_directory_checksum;

class SecurityFixerTest extends QITTestCase {
	use MatchesSnapshots;

	/** @var Filesystem */
	protected $filesystem;
	protected $tmp_dir;

	public function setUp(): void {
		parent::setUp();

		$this->filesystem = new Filesystem();
		$this->tmp_dir    = sys_get_temp_dir() . '/wooqit-tests';
		$this->create_tmp_dir();
	}

	public function tearDown(): void {
		$this->delete_tmp_dir();
		parent::tearDown();
	}

	protected function delete_tmp_dir() {
		clearstatcache();

		if ( file_exists( $this->tmp_dir ) ) {
			$this->filesystem->remove( $this->tmp_dir );
		}
	}

	protected function create_tmp_dir() {
		$this->delete_tmp_dir();
		mkdir( $this->tmp_dir, 0777, true );
		clearstatcache();
		if ( ! file_exists( $this->tmp_dir ) ) {
			throw new RuntimeException( 'Could not create temporary directory.' );
		}
	}

	protected function recursive_copy( $src, $dst ) {
		$dir = new RecursiveDirectoryIterator( $src, FilesystemIterator::SKIP_DOTS );
		/** @var DirectoryIterator $iterator */
		$iterator = new RecursiveIteratorIterator( $dir, RecursiveIteratorIterator::SELF_FIRST );

		foreach ( $iterator as $item ) {
			if ( $item->isDir() ) {
				mkdir( $dst . DIRECTORY_SEPARATOR . $iterator->getSubPathName() );
			} else {
				copy( $item, $dst . DIRECTORY_SEPARATOR . $iterator->getSubPathName() );
			}
		}
	}

	protected function get_test_result_json(): string {
		return json_decode( file_get_contents( __DIR__ . '/data/security_fixer_result.json' ), true )['test_result_json'];
	}

	public function test_security_fixer_nonexisting_plugin_dir(): void {
		$test_result_json = $this->get_test_result_json();
		$plugin_dir       = __DIR__ . '/data/plugins/woocommerce-example-plugin2';

		$this->expectException( SecurityFixerException::class );
		$this->expectExceptionMessage( SecurityFixerException::plugin_dir_not_found( $plugin_dir )->getMessage() );

		$security_fixer = App::make( SecurityFixer::class );

		$security_fixer->fix( $plugin_dir, $test_result_json );
	}

	public function test_security_fixer_invalid_result_json(): void {
		$test_result_json = 'not-json';
		$plugin_dir       = __DIR__ . '/data/plugins/woocommerce-example-plugin';

		$this->expectException( SecurityFixerException::class );
		$this->expectExceptionMessage( SecurityFixerException::test_result_json_invalid()->getMessage() );

		$security_fixer = App::make( SecurityFixer::class );

		$security_fixer->fix( $plugin_dir, $test_result_json );
	}

	protected function make_find_file_correspondence_sut() {
		return new class extends SecurityFixer {
			public function find_file_correspondence( string $file_in_json, string $local_plugin_dir ): string {
				return parent::find_file_correspondence( $file_in_json, $local_plugin_dir );
			}
		};
	}

	/**
	 * This function tests the application of security fixes to a sample plugin.
	 *
	 * The plugin tested is a copy of the 'woocommerce-example-plugin'. The copy is
	 * created in a temporary directory to prevent alterations to the original code.
	 *
	 * The test performs the following steps:
	 *
	 * 1. Asserts that a specific piece of insecure code is present in one of the plugin's files.
	 * 2. Runs the security fixer, which should change the insecure code into secure code.
	 * 3. Asserts that the insecure code has been changed to secure code.
	 * 4. Computes a checksum of the entire plugin directory.
	 * 5. Asserts that the calculated checksum matches an expected value.
	 *
	 * The checksum is a form of "snapshot test". It creates a unique signature for the
	 * contents of the plugin directory. The expected value is a checksum that the
	 * developer has manually verified as correct and secure. By comparing the calculated
	 * checksum to the expected value, the test can verify that the security fixer has
	 * modified the plugin directory exactly as expected, and no more or less.
	 *
	 * Note that the checksum takes into account the contents of the files, but not their
	 * names or paths. Therefore, if a file is renamed or moved without changing its
	 * contents, the checksum will remain the same.
	 *
	 * If the checksums do not match, this means that the contents of the plugin directory
	 * are not exactly what the developer expects. This could be due to an error in the
	 * security fixer, or it could mean that the expected checksum needs to be updated.
	 *
	 * As a result, the checksum verification serves as a powerful tool for catching
	 * unexpected changes and ensuring the correctness of the security fixer's operation.
	 */
	public function test_security_fixer(): void {
		$test_result_json = $this->get_test_result_json();
		$plugin_dir       = __DIR__ . '/data/plugins/woocommerce-example-plugin';

		// Create a copy of the plugin directory in the tmp directory
		$tmp_plugin_dir = $this->tmp_dir . '/woocommerce-example-plugin';

		$this->filesystem->mirror( $plugin_dir, $tmp_plugin_dir );

		// Assert has security issue.
		$this->assertStringContainsString( <<<'CODE'
printf( __( 'Hi %s,', 'woocommerce-example-plugin' ), esc_html( $recipient_name ) );
CODE
			, file_get_contents( $tmp_plugin_dir . '/templates/emails/refunds-plain.php' ) );

		App::make( SecurityFixer::class )->fix( $tmp_plugin_dir, $test_result_json );

		// Assert fixed.
		$this->assertStringContainsString( <<<'CODE'
printf( esc_html__( 'Hi %s,', 'woocommerce-example-plugin' ), esc_html( $recipient_name ) );
CODE
			, file_get_contents( $tmp_plugin_dir . '/templates/emails/refunds-plain.php' ) );

		// Calculate checksum after the fix.
		$checksum = calculate_directory_checksum( $tmp_plugin_dir );

		$this->assertMatchesJsonSnapshot( json_encode( $checksum, JSON_PRETTY_PRINT ) );
	}

	public function test_find_file_correspondence_with_existing_file() {
		$sut = $this->make_find_file_correspondence_sut();

		$file_in_json            = '/home/runner/work/staging-compatibility-dashboard/staging-compatibility-dashboard/ci/plugins/woocommerce-example-plugin/woocommerce-example-plugin.php';
		$local_plugin_dir        = $this->tmp_dir . '/woocommerce-example-plugin';
		$expected_local_filepath = $this->tmp_dir . '/woocommerce-example-plugin/woocommerce-example-plugin.php';

		// Create necessary directories and the file for the test.
		mkdir( $local_plugin_dir, 0777, true );
		touch( $expected_local_filepath );

		$result_file_path = $sut->find_file_correspondence( $file_in_json, $local_plugin_dir );
		$this->assertEquals( $expected_local_filepath, $result_file_path );
	}

	public function test_find_file_correspondence_with_non_existing_file() {
		$sut = $this->make_find_file_correspondence_sut();

		$filename = bin2hex( random_bytes( 10 ) ) . '.php';

		$file_in_json            = "/home/runner/work/staging-compatibility-dashboard/staging-compatibility-dashboard/ci/plugins/woocommerce-example-plugin/$filename";
		$local_plugin_dir        = $this->tmp_dir . '/woocommerce-example-plugin';
		$expected_local_filepath = $this->tmp_dir . "/woocommerce-example-plugin/$filename";

		// Make sure the necessary directory exists for the test.
		mkdir( $local_plugin_dir, 0777, true );

		$this->expectExceptionObject( SecurityFixerException::file_does_not_exist_locally( $expected_local_filepath ) );
		$sut->find_file_correspondence( $file_in_json, $local_plugin_dir );
	}

	public function test_find_file_correspondence_with_spaces_in_file_path() {
		$sut = $this->make_find_file_correspondence_sut();

		$file_in_json            = '/home/runner/work/staging-compatibility-dashboard/staging-compatibility-dashboard/ci/plugins/woo commerce-example-plugin/woocommerce-example-plugin.php';
		$local_plugin_dir        = $this->tmp_dir . '/woo commerce-example-plugin';
		$expected_local_filepath = $this->tmp_dir . '/woo commerce-example-plugin/woocommerce-example-plugin.php';

		// Create necessary directories and the file for the test.
		mkdir( $local_plugin_dir, 0777, true );
		touch( $expected_local_filepath );

		$result_file_path = $sut->find_file_correspondence( $file_in_json, $local_plugin_dir );
		$this->assertEquals( $expected_local_filepath, $result_file_path );
	}

	public function test_find_file_correspondence_with_mismatched_top_level_directory() {
		$sut = $this->make_find_file_correspondence_sut();

		$file_in_json            = '/home/runner/work/staging-compatibility-dashboard/staging-compatibility-dashboard/ci/plugins/woocommerce-example-plugin/woocommerce-example-plugin.php';
		$local_plugin_dir        = $this->tmp_dir . '/different-plugin-name';
		$expected_local_filepath = $this->tmp_dir . '/different-plugin-name/woocommerce-example-plugin.php';

		// Create necessary directories and the file for the test.
		mkdir( $local_plugin_dir, 0777, true );
		touch( $expected_local_filepath );

		$result_file_path = $sut->find_file_correspondence( $file_in_json, $local_plugin_dir );
		$this->assertEquals( $expected_local_filepath, $result_file_path );
	}

	public function test_fix_on_mismatched_directory(): void {
		$test_result_json = $this->get_test_result_json();

		mkdir( $this->tmp_dir . '/not-the-sut' );

		// Prevent warnings from being printed on the Console.
		App::bind( Output::class, NullOutput::class );

		$this->expectException( SecurityFixerException::class );
		$this->expectExceptionMessage( SecurityFixerException::too_many_files_not_found()->getMessage() );

		App::make( SecurityFixer::class )->fix( $this->tmp_dir . '/not-the-sut', $test_result_json );
	}
}
