<?php
namespace QIT_AI_Webserver\Tools;

use QIT_AI_Webserver\Lib\FactStore;
use LLPhant\Chat\FunctionInfo\FunctionInfo;
use LLPhant\Chat\FunctionInfo\Parameter;

class SearchFactsTool extends BaseTool {
	public function getName(): string        { return 'search_facts'; }
	public function getDescription(): string { return 'Semantic / full‑text search over prior facts'; }

	public function getFunctionInfo(): FunctionInfo {
		$params = [
			new Parameter('query', 'string',  'Search phrase (required)'),
			new Parameter('k',     'integer', 'Max results (default 5)')
		];
		return new FunctionInfo(
			$this->getName(),
			[$this, 'search_facts'],
			$this->getDescription(),
			$params,
			[ $params[0] ]   // query is required
		);
	}

	public function search_facts(string $query, int $k = 5): string {
		return json_encode(
			$this->execute(compact('query', 'k')),
			JSON_UNESCAPED_SLASHES
		);
	}

	protected function do(array $p) {
		return [
			'results'   => FactStore::search($p['query'], $p['k'] ?? 5),
			'truncated' => false
		];
	}
}