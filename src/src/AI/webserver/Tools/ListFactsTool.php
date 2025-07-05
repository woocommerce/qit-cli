<?php
namespace QIT_AI_Webserver\Tools;

use QIT_AI_Webserver\Lib\FactStore;
use LLPhant\Chat\FunctionInfo\FunctionInfo;
use LLPhant\Chat\FunctionInfo\Parameter;

class ListFactsTool extends BaseTool {
	public function getName(): string         { return 'list_facts'; }
	public function getDescription(): string  { return 'Browse previous investigation facts'; }

	/* ---- LLPhant metadata ---- */
	public function getFunctionInfo(): FunctionInfo {
		$params = [
			new Parameter('step',  'integer', 'Filter by step index (optional)'),
			new Parameter('kind',  'string',  'Filter by fact kind   (optional)'),
			new Parameter('limit', 'integer', 'Max items to return (default 20)')
		];
		return new FunctionInfo(
			$this->getName(),
			[$this, 'list_facts'],
			$this->getDescription(),
			$params,
			[]  // no required params
		);
	}

	public function list_facts(?int $step = null,
	                           ?string $kind = null,
	                           int $limit = 20): string {
		return json_encode(
			$this->execute(compact('step', 'kind', 'limit')),
			JSON_UNESCAPED_SLASHES
		);
	}

	protected function do(array $p) {
		return [
			'results'   => FactStore::list($p['step'] ?? null,
			                               $p['kind'] ?? null,
			                               $p['limit'] ?? 20),
			'truncated' => false
		];
	}
}