<?php
namespace gossi\formatter\parser;

use phootwork\tokenizer\Token;
use phootwork\tokenizer\TokenCollection;
use phootwork\tokenizer\TokenVisitorInterface;

class TokenTracker implements TokenVisitorInterface {

	private $tokens;
	private $context;

	private $next;
	private $prev;

	public function __construct(TokenCollection $tokens, Context $contextManager) {
		$this->tokens = $tokens;
		$this->context = $contextManager;
		$this->context->setTracker($this);
	}

	public function visitToken(Token $token) {
		$this->next = $this->nextToken($token);
		$this->prev = $this->prevToken($token);
		$this->context->visitToken($token);
	}

	public function getNextToken() {
		return $this->next;
	}

	public function getPrevToken() {
		return $this->prev;
	}

	public function nextToken(Token $token, $offset = 1) {
		$index = $this->tokens->indexOf($token);
		$index += $offset;
		$token = $this->tokens->get($index);

		if ($token === null) {
			$token = new Token();
		}

		return $token;
	}

	public function prevToken($token, $offset = 1) {
		$index = $this->tokens->indexOf($token);
		$index -= $offset;
		$token = $this->tokens->get($index);

		if ($token === null) {
			$token = new Token();
		}

		return $token;
	}

	public function isLastToken(Token $token) {
		return $this->tokens->indexOf($token) == $this->tokens->size() - 1;
	}

}
