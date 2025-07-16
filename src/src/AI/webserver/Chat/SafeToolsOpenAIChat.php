<?php

namespace QIT_AI_Webserver\Chat;

use LLPhant\Chat\OpenAIChat;
use OpenAI\Responses\Chat\CreateResponse;

class SafeToolsOpenAIChat extends OpenAIChat {
	/** @var string[] */
	private array $unknown_tools = [];

	/**
	 * Overridden; now tolerant.
	 * (Signature matches the parent – visibility already made protected by the patch.)
	 * @return array<mixed>
	 */
	protected function getToolsToCall( CreateResponse $answer ): array {
		$valid               = [];
		$this->unknown_tools = [];

		foreach ( $answer->choices[0]->message->toolCalls as $tc ) {
			$name  = $tc->function->name;
			$found = false;

			foreach ( $this->tools as $fn ) {
				if ( $fn->name === $name ) {
					$fi = $fn->cloneWithId( $tc->id );
					// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					$fi->jsonArgs = $tc->function->arguments;
					$valid[]      = $fi;
					$found        = true;
					break;
				}
			}

			if ( ! $found ) {
				$this->unknown_tools[] = $name;
			}
		}

		return $valid;
	}

	/**
	 * @return array<string>
	 */
	public function getUnknownTools(): array {
		return $this->unknown_tools;
	}
}
