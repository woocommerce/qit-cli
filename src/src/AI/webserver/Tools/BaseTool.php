<?php
namespace QIT_AI_Webserver\Tools;

use Exception;
use QIT_AI_Webserver\Lib\DebugLogger;
use QIT_AI_Webserver\Lib\FilePathResolver;

abstract class BaseTool implements ToolInterface
{
    protected FilePathResolver $r;
    protected string $workDir;

    public function __construct(string $workDirectory)
    {
        $this->workDir = rtrim($workDirectory, '/\\');
        $this->r       = new FilePathResolver($this->workDir);
    }

    /** ----------------------------------------------------------------
     *  Child classes must implement the "real" work here.
     *  On success return ANY serialisable value.
     *  @throws \Throwable to trigger error envelope
     *-----------------------------------------------------------------*/
    abstract protected function do(array $params);

    public function execute(array $params): array
    {
        try {
            $data = $this->do($params);

            return [
                'success'   => true,
                'data'      => $data,
                'truncated' => $data['truncated'] ?? false,
                'error'     => null,
                'debug'     => [],
            ];
        } catch (Exception|\Throwable $e) {
            DebugLogger::log(static::class . '_error', [
                'args'  => $params,
                'error' => $e->getMessage(),
                'tree'  => DebugLogger::dirTree($this->workDir, 2, 150),
            ]);

            return [
                'success'   => false,
                'data'      => null,
                'truncated' => false,
                'error'     => $e->getMessage(),
                'debug'     => ['args' => $params],
            ];
        }
    }
}