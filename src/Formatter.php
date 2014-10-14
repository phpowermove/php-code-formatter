<?php
namespace gossi\formatter;

use gossi\formatter\token\Tokenizer;
use gossi\formatter\config\Config;
use gossi\formatter\formatters\DelegateFormatter;
use gossi\formatter\parser\Lexer;
use gossi\formatter\parser\Analyzer;

class Formatter {

	private $config;

	public function __construct($profile = null) {
		$this->config = new Config($profile);
	}

	public function format($code) {
		// get tokens
		$tokenizer = new Tokenizer();
		$tokens = $tokenizer->tokenize($code);
		
		// preparations
		$lexer = new Lexer();
		$tokens = $lexer->fix($tokens);
		$tokens = $lexer->filterTokens($tokens);
		
		$analyzer = new Analyzer($tokens);
		$analyzer->analyze();

		// formatting
		$visitor = new DelegateFormatter($tokens, $this->config);
		foreach ($tokens as $token) {
			$token->accept($visitor);
		}
		
		// post processing
		
		return $visitor->getCode();
	}
}
