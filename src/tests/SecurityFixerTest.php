<?php

use QIT_CLI\App;
use QIT_CLI\Fixer\Exceptions\SecurityFixerException;
use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Component\Filesystem\Filesystem;

class SecurityFixerTest extends \QIT_CLI_Tests\QITTestCase {
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

		$security_fixer = App::make( \QIT_CLI\Fixer\SecurityFixer::class );

		$security_fixer->fix( $plugin_dir, $test_result_json );
	}

	public function test_security_fixer_invalid_result_json(): void {
		$test_result_json = 'not-json';
		$plugin_dir       = __DIR__ . '/data/plugins/woocommerce-example-plugin';

		$this->expectException( SecurityFixerException::class );
		$this->expectExceptionMessage( SecurityFixerException::test_result_json_invalid()->getMessage() );

		$security_fixer = App::make( \QIT_CLI\Fixer\SecurityFixer::class );

		$security_fixer->fix( $plugin_dir, $test_result_json );
	}

	protected function make_find_file_correspondence_sut() {
		return new class extends \QIT_CLI\Fixer\SecurityFixer {
			public function find_file_correspondence( string $file_in_json, string $local_plugin_dir ): string {
				return parent::find_file_correspondence( $file_in_json, $local_plugin_dir );
			}
		};
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

		$this->expectExceptionObject( SecurityFixerException::plugin_path_does_not_match( 'different-plugin-name', 'woocommerce-example-plugin' ) );
		$sut->find_file_correspondence( $file_in_json, $local_plugin_dir );
	}

	public function test_security_fixer(): void {
		$test_result_json = $this->get_test_result_json();
		$plugin_dir       = __DIR__ . '/data/plugins/woocommerce-example-plugin';

		// Create a copy of the plugin directory in the tmp directory
		$tmp_plugin_dir = $this->tmp_dir . '/woocommerce-example-plugin';

		$this->filesystem->mirror( $plugin_dir, $tmp_plugin_dir );

		$security_fixer = App::make( \QIT_CLI\Fixer\SecurityFixer::class );

		$security_fixer->fix( $tmp_plugin_dir, $test_result_json );
	}
}
