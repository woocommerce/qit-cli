<?php
namespace QIT_CLI\PreCommand;

use QIT_CLI\PreCommand\Pipeline\PipelineContext;
class PrecommandEarlyReturn extends \Exception {
		/** @var PipelineContext */
		protected $context;
	public function set_context( PipelineContext $context ): void {
			$this->context = $context;
	}
	public function get_context(): PipelineContext {
			return $this->context;
	}
}
