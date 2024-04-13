<?php

namespace QIT_CLI\Commands\CustomTests;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\IO\Output;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Process\Process;
use function QIT_CLI\open_in_browser;

class ShowReportCommand extends Command
{
    protected static $defaultName = 'e2e-report'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

    /** @var Cache */
    protected $cache;

    public function __construct(Cache $cache)
    {
        parent::__construct();
        $this->cache = $cache;
    }

    protected function configure()
    {
        $this
            ->addArgument('report_dir', InputArgument::OPTIONAL, '(Optional) The report directory. If not set, will show the last report.')
            ->addOption('dir_only', null, null, 'Only show the report directory.')
            ->setDescription('Shows a test report.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!is_null($input->getArgument('report_dir'))) {
            $report_dir = $input->getArgument('report_dir');
        } else {
            $report_dir = $this->cache->get('last_e2e_report');
        }

        if (!file_exists($report_dir)) {
            throw new \RuntimeException(sprintf('Could not find the report directory: %s', $report_dir));
        }

        if (!file_exists($report_dir . '/index.html')) {
            throw new \RuntimeException(sprintf('Could not find the report file: %s', $report_dir . '/index.html'));
        }

        if ($input->getOption('dir_only')) {
            // We usually want the "HTML" report, but here print the general result directory.
            $report_dir = dirname($report_dir);

            $output->writeln($report_dir);

            return Command::SUCCESS;
        }

        try {
            $port = $this->start_server($report_dir);
            echo "Server started on port: $port\n";
        } catch (\RuntimeException $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }

        open_in_browser("http://localhost:$port");

        (new QuestionHelper())->ask(App::make(InputInterface::class), App::make(Output::class), new Question("Report available on http://localhost:$port. Press Ctrl+C to quit."));

        return Command::SUCCESS;
    }

    protected function start_server($report_dir, $start_port = 0): int
    {
        $max_tries = 10; // Maximum number of ports to try before giving up.
        $port = $start_port;
        $results_process = new Process([PHP_BINARY, '-S', "localhost:$port", '-t', $report_dir]);
        $results_process->start();

        $waited = 0;
        while ($results_process->isRunning()) {
            usleep(200000); // wait 0.2 seconds.
            $waited += 0.2;
            if ($waited > 30) { // Timeout after 30 seconds of waiting.
                $results_process->stop(); // Stop the current process.
                if ($port === 0 || $port >= 8000 + $max_tries) {
                    throw new \RuntimeException('Could not start the server on any port.');
                }
                // If the system-assigned port failed, start from 8000.
                return $this->start_server($report_dir, 8000);
            }

            // Check for a message indicating the server has started.
            if (preg_match('/Development Server \(http:\/\/localhost:(\d+)\) started/', $results_process->getErrorOutput(), $matches)) {
                return (int)$matches[1]; // Return the port number on success.
            }
        }

        if (!$results_process->isSuccessful()) {
            echo $results_process->getOutput() . "\n" . $results_process->getErrorOutput();
        }

        $new_port = $port === 0 ? 8000 : $port + 1;

        // If we exit the loop without having found a port, increment the port number and try again.
        return $this->start_server($report_dir, $new_port);
    }
}
