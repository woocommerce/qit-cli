<?php

namespace QIT_AI_Webserver\Tools;

use LLPhant\Chat\FunctionInfo\FunctionInfo;
use LLPhant\Chat\FunctionInfo\Parameter;

class ListFactsTool extends BaseTool {

	public function get_name(): string {
		return 'list_facts';
	}

	public function get_description(): string {
		return 'List all available facts';
	}

	public function getFunctionInfo(): FunctionInfo {
		$params = [
			new Parameter( 'limit', 'integer', 'Maximum number of facts to return (default: 50)' ),
			new Parameter( 'category', 'string', 'Filter by category (optional)' ),
			new Parameter( 'type', 'string', 'Filter by type (optional)' ),
		];

		return new FunctionInfo(
			$this->get_name(),
			[ $this, 'list_all_facts' ],
			$this->get_description(),
			$params,
			[]           // no required parameters
		);
	}

	public function get_function_info(): FunctionInfo {
		return $this->getFunctionInfo();
	}

	public function list_all_facts(
		int $limit = 50,
		?string $category = null,
		?string $type = null
	): string {
		$result = $this->execute( compact( 'limit', 'category', 'type' ) );

		return json_encode( $result, JSON_UNESCAPED_SLASHES );
	}

	/**
	 * @param array<string, mixed> $p
	 * @return array<string, mixed>
	 */
	protected function do( array $p ) {
		$limit    = $p['limit'] ?? 50;
		$category = $p['category'] ?? null;
		$type     = $p['type'] ?? null;

		$fact_store = $this->context ? $this->context->fact_store : null;
		if ( ! $fact_store ) {
			return [
				'facts' => [],
				'total' => 0,
				'error' => 'No fact store available',
			];
		}

		$filters = [];
		if ( $category ) {
			$filters['category'] = $category;
		}
		if ( $type ) {
			$filters['type'] = $type;
		}

		$facts = $fact_store->list_all( $filters, $limit );

		return [
			'facts' => $facts,
			'total' => count( $facts ),
		];
	}
}
