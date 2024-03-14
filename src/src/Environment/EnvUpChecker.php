<?php

namespace QIT_CLI\Environment;

use QIT_CLI\App;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function QIT_CLI\is_windows;
use function QIT_CLI\is_wsl;

class EnvUpChecker {
	/** @var OutputInterface */
	protected $output;

	public function __construct( OutputInterface $output ) {
		$this->output = $output;
	}

	public function check_and_render( E2EEnvInfo $env_info ): void {
		if ( ! $this->check_site( $env_info->site_url ) ) {
			$io = new SymfonyStyle( App::make( InputInterface::class ), $this->output );

			$site_url_domain = parse_url( $env_info->site_url, PHP_URL_HOST );
			$io->section( 'Test connection failed' );

			if ( $env_info->domain !== 'localhost' ) {
				$io->writeln( 'We couldn\'t access the website. To fix this, please check if the following line is present in your hosts file:' );
				$io->writeln( sprintf( "\n<info>127.0.0.1 %s</info>\n", $site_url_domain ) );
				if ( is_wsl() ) {
					// Inside Windows WSL.
					$io->writeln( 'It appears you are using Windows Subsystem for Linux (WSL). To update the hosts file automatically, you can run the following command in PowerShell with administrative privileges, outside of WSL:' );
					$io->writeln( sprintf( 'Add-Content -Path $env:windir\System32\drivers\etc\hosts -Value "`n127.0.0.1`t%s" -Force', $site_url_domain ) );
				} elseif ( is_windows() ) {
					// In native Windows.
					$io->writeln( 'If it\'s not, you can add it using this PowerShell command with Administration privileges:' );
					$io->writeln( sprintf( 'Add-Content -Path $env:windir\System32\drivers\etc\hosts -Value "`n127.0.0.1`t%s" -Force', $site_url_domain ) );
				} else {
					$io->writeln( 'If it\'s not, you can add it using this command:' );
					$io->writeln( sprintf( "\n<info>echo \"127.0.0.1 %s\" | sudo tee -a /etc/hosts</info>", $site_url_domain ) );
				}
			}
		}
	}

	protected function check_site( string $site_url ): bool {
		if ( $this->output->isVerbose() ) {
			$this->output->write( sprintf( 'Checking if %s is accessible...', $site_url ) );
		}

		$ch = curl_init( $site_url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
		curl_setopt( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3' );
		curl_exec( $ch );
		$http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		curl_close( $ch );

		if ( $this->output->isVerbose() ) {
			$this->output->write( sprintf( " HTTP Code: %d\n", $http_code ) );
		}

		return $http_code === 200;
	}
}
