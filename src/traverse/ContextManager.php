<?php
namespace gossi\formatter\traverse;

use gossi\formatter\token\Token;
use gossi\collection\Stack;
use gossi\formatter\token\Tokenizer;
use gossi\formatter\token\TokenVisitor;

class ContextManager implements TokenVisitor {

	const LEXICAL_BLOCK = 'block';
	const LEXICAL_CALL = 'call';
	const LEXICAL_GROUP = 'group';
	
	const BLOCK_NAMESPACE = 'namespace';
	const BLOCK_USE = 'use';
	const BLOCK_FUNCTION = 'function';
	const BLOCK_STRUCT = 'struct';
	const BLOCK_METHOD = 'method';
	const BLOCK_PROPERTIES = 'properties';
	const BLOCK_CONST = 'constants';

	// current contexts
	private $structural;
	private $parens;
	private $parensToken;

	// stacks
	private $structuralStack;
	private $parensStack;
	private $parensTokenStack;
	private $line;

	// helpers
	private $structuralDetected;
	private $tracker;
	
	public function __construct() {
		$this->structuralStack = new Stack();
		$this->parensStack = new Stack();
		$this->parensTokenStack = new Stack();
		$this->semanticBlocks = array_merge(Tokenizer::$STRUCTURAL, 
				[T_PUBLIC, T_PRIVATE, T_PROTECTED, T_ABSTRACT, T_STATIC, T_VAR]);
	}
	
	public function setTracker(TokenTracker $tracker) {
		$this->tracker = $tracker;
	}

	public function visit(Token $token) {
		// load current contexts
		$this->structural = $this->peekStructural();
		$this->parens = $this->parensStack->peek();
		$this->parensToken = $this->peekParensToken();
		
		// detect new contexts
		$this->detectStructuralContext($token);
		$this->detectParensContext($token);
		$this->detectLineContext($token);
	}

	private function detectStructuralContext(Token $token) {
		if (in_array($token->type, Tokenizer::$BLOCKS)
				|| in_array($token->type, Tokenizer::$STRUCTURAL)) {
			$this->structuralDetected = $token;
		}

		// push structural context when entering
		if ($token->contents == '{') { 
			$this->structuralStack->push($this->structuralDetected);
			$this->structural = $this->structuralDetected;
			$this->structuralDetected = null;
		}

		// popping structural context
		if ($token->contents == '}') {
			$this->structural = $this->structuralStack->pop();
		}
		
		// neglect structural detection
		if ($this->structuralDetected !== null && $token->contents == ';' &&
				!($this->parens == self::LEXICAL_BLOCK || $this->parens == self::LEXICAL_GROUP)) {
			$this->structuralDetected = null;
		}
	}
	
	public function isStructuralContextDetected() {
		return $this->structuralDetected !== null;
	}
	
	private function detectParensContext(Token $token) {
		$prevToken = $this->tracker->getPrevToken();
		if ($token->contents == '(') {
			if (in_array($prevToken->type, Tokenizer::$BLOCKS)
					|| in_array($prevToken->type, Tokenizer::$OPERATORS)) {
				$this->parens = self::LEXICAL_BLOCK;
				$this->parensToken = $prevToken;
			} else if ($this->isFunctionInvocation($token)) {
				$this->parens = self::LEXICAL_CALL;
				$this->parensToken = $prevToken;
			} else {
				$this->parens = self::LEXICAL_GROUP;
				$this->parensToken = new Token();
			}

			$this->parensStack->push($this->parens);
			$this->parensTokenStack->push($this->parensToken);
		} else if ($token->contents == ')') {
			$this->parens = $this->parensStack->pop();
			$this->parensToken = $this->parensTokenStack->pop();
		}
	}
	
	private function isFunctionInvocation($token) {
		$prevToken = $this->tracker->getPrevToken();
		return $token->contents == '(' && $prevToken->type == T_STRING;
	}
	
	private function detectLineContext(Token $token) {
		if (in_array($token->contents, Tokenizer::$LINE_CONTEXT)) {
			$this->line = $token->contents;
		}
	}
	
	public function resetLineContext() {
		$this->line = null;
	}
	
	public function getStructuralContext() {
		return $this->structural;
	}
	
	public function getParensContext() {
		return $this->parens;
	}
	
	public function getParensTokenContext() {
		return $this->parensToken;
	}
	
	private function peekStructural() {
		if ($this->structuralStack->size() > 0) {
			return $this->structuralStack->peek();
		}
		return new Token();
	}
	
	private function peekParensToken() {
		if ($this->parensTokenStack->size() > 0) {
			return $this->parensTokenStack->peek();
		}
		return new Token();
	}
}