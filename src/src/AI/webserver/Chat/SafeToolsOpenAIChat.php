<?php

namespace QIT_AI_Webserver\Chat;

use LLPhant\Chat\OpenAIChat;
use OpenAI\Responses\Chat\CreateResponse;

class SafeOpenAIChat extends OpenAIChat {
	/** @var string[] */
	private array $unknownTools = [];

	/**
	 * Overridden; now tolerant.
	 * (Signature matches the parent – visibility already made protected by the patch.)
	 */
	protected function getToolsToCall( CreateResponse $answer ): array {
		$valid              = [];
		$this->unknownTools = [];

		foreach ( $answer->choices[0]->message->toolCalls as $tc ) {
			$name  = $tc->function->name;
			$found = false;

			foreach ( $this->tools as $fn ) {
				if ( $fn->name === $name ) {
					$fi           = $fn->cloneWithId( $tc->id );
					$fi->jsonArgs = $tc->function->arguments;
					$valid[]      = $fi;
					$found        = true;
					break;
				}
			}

			if ( ! $found ) {
				$this->unknownTools[] = $name;
			}
		}

		return $valid;
	}

	public function getUnknownTools(): array {
		return $this->unknownTools;
	}
}
