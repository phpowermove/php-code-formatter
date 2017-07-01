<?php
namespace gossi\formatter\parser;

use phootwork\tokenizer\PhpTokenizer;
use phootwork\tokenizer\TokenCollection;

class Parser {

	/** @var PhpTokenizer */
	private $tokenizer;
	/** @var Lexer */
	private $lexer;
	/** @var Analyzer */
	private $analyzer;
	/** @var Context */
	private $context;
	/** @var TokenTracker */
	private $tracker;
	/** @var TokenCollection */
	private $tokens;
	/** @var TokenMatcher */
	private $matcher;

	public function __construct() {
		$this->matcher = new TokenMatcher();
		$this->tokenizer = new PhpTokenizer();
		$this->lexer = new Lexer();
		$this->context = new Context($this);
		$this->analyzer = new Analyzer($this);
	}

	public function parse($code) {
		// get tokens
		$tokens = $this->tokenizer->tokenize($code);

		// preparations
		$tokens = $this->lexer->filterTokens($tokens);
		$tokens = $this->lexer->repair($tokens);

		// helpers
		$this->tracker = new TokenTracker($tokens, $this->context);
		$this->tokens = $tokens;

		// analyze
		$this->analyzer->analyze($tokens);
		$this->context->reset();
	}

	/**
	 * 
	 * @return TokenCollection
	 */
	public function getTokens() {
		return $this->tokens;
	}

	/**
	 * @return TokenTracker
	 */
	public function getTracker() {
		return $this->tracker;
	}

	/**
	 * @return Context
	 */
	public function getContext() {
		return $this->context;
	}

	/**
	 * @return Analyzer
	 */
	public function getAnalyzer() {
		return $this->analyzer;
	}

	/**
	 * @return TokenMatcher
	 */
	public function getMatcher() {
		return $this->matcher;
	}

}
