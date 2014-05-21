<?php
namespace gossi\formatter;

use gossi\formatter\token\Tokenizer;
use gossi\formatter\visitor\Visitor;
use gossi\formatter\config\Config;

class Formatter {
	
	private $config;
	
	public function __construct($profile = null) {
		$this->config = new Config($profile);
	}
	
	public function format($code) {
		$tokenizer = new Tokenizer();
		$tokens = $tokenizer->tokenize($code);
		$visitor = new Visitor($tokens, $this->config);
		
		foreach ($tokens as $token) {
			$token->accept($visitor);
		}
		
		return $visitor->getCode();
	}
}