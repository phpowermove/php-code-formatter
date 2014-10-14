<?php
namespace gossi\formatter\traverse;

use gossi\formatter\token\TokenCollection;
use gossi\formatter\token\Token;
use gossi\formatter\token\TokenVisitorInterface;

class TokenTracker implements TokenVisitorInterface {
	
	private $tokens;
	private $context;
	
	private $next;
	private $prev;
	
	public function __construct(TokenCollection $tokens, ContextManager $contextManager) {
		$this->tokens = $tokens;
		$this->context = $contextManager;
		$this->context->setTracker($this);
	}

	public function visit(Token $token) {
		$this->next = $this->nextToken($token);
		$this->prev = $this->prevToken($token);
		$this->context->visit($token);
	}

	public function getNextToken() {
		return $this->next;
	}
	
	public function getPrevToken() {
		return $this->prev;
	}

	public function nextToken(Token $token, $offset = 1) {
		$i = 1;
		$index = $this->tokens->indexOf($token) - 1;
		do {
			list($index, $t) = $this->tokens->nextToken($index);
		} while ($i++ <= $offset);
		
		if (empty($t)) {
			$t = new Token();
		}
		return $t;
	}
	
	public function prevToken($token, $offset = 1) {
		$i = 1;
		$index = $this->tokens->indexOf($token) + 1;
		do {
			list($index, $t) = $this->tokens->prevToken($index);
		} while ($i++ <= $offset);
		
		if (empty($t)) {
			$t = new Token();
		}
		return $t;
	}

	public function isLastToken(Token $token) {
		return $this->tokens->indexOf($token) == $this->tokens->size() - 1;
	}

}