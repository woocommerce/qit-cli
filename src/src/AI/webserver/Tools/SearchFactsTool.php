<?php

namespace QIT_AI_Webserver\Tools;

use LLPhant\Chat\FunctionInfo\FunctionInfo;
use LLPhant\Chat\FunctionInfo\Parameter;

class SearchFactsTool extends BaseTool {

	public function get_name(): string {
		return 'search_facts';
	}

	public function get_description(): string {
		return 'Search through available facts';
	}

	public function getFunctionInfo(): FunctionInfo {
		$params = [
			new Parameter( 'query', 'string', 'Search query' ),
			new Parameter( 'limit', 'integer', 'Maximum number of results (default: 20)' ),
		];

		return new FunctionInfo(
			$this->get_name(),
			[ $this, 'search_facts' ],
			$this->get_description(),
			$params,
			[ 'query' ]      // required parameters
		);
	}

	public function get_function_info(): FunctionInfo {
		return $this->getFunctionInfo();
	}

	public function search_facts(
		string $query,
		int $limit = 20
	): string {
		$result = $this->execute( compact( 'query', 'limit' ) );

		return json_encode( $result, JSON_UNESCAPED_SLASHES );
	}

	/**
	 * @param array<string, mixed> $p
	 * @return array<string, mixed>
	 */
	protected function do( array $p ) {
		$query = $p['query'];
		$limit = $p['limit'] ?? 20;

		$fact_store = $this->context ? $this->context->fact_store : null;
		if ( ! $fact_store ) {
			return [
				'results' => [],
				'total'   => 0,
				'error'   => 'No fact store available',
			];
		}

		$results = $fact_store->search( $query, $limit );

		return [
			'results'   => $results,
			'total'     => count( $results ),
			'query'     => $query,
			'truncated' => count( $results ) >= $limit,
		];
	}
}
