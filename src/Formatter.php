<?php
namespace gossi\formatter;

use gossi\formatter\token\Tokenizer;
use gossi\formatter\visitor\Visitor;
use gossi\formatter\config\Config;
use gossi\formatter\formatters\FormatterDelegator;

class Formatter {
	
	private $config;
	
	public function __construct($profile = null) {
		$this->config = new Config($profile);
	}
	
	public function format($code) {
		$tokenizer = new Tokenizer();
		$tokens = $tokenizer->tokenize($code);
		$visitor = new FormatterDelegator($tokens, $this->config);
		
// 		print_r($tokens);
		
		foreach ($tokens as $token) {
			$token->accept($visitor);
		}
		
		return $visitor->getCode();
	}
}