<?php

namespace gossi\formatter\visitor;

use gossi\formatter\token\Token;
use gossi\formatter\token\TokenCollection;
use gossi\formatter\utils\Writer;
use gossi\formatter\config\Config;

class Visitor {
	
	private $tokens;
	private $config;
	private $writer;
	private $context = [];
	
	public function __construct(TokenCollection $tokens, Config $config) {
		$this->tokens = $tokens;
		$this->config = $config;
		$this->writer = new Writer([
			'indentation_character' => $config->getIndentation('character'),
			'indentation_size' => $config->getIndentation('size')
		]);
	}
	
	public function visit(Token $token) {
		// token types in collections
		
		
		// switch between individual token types
		switch ($token->type) {
// 			case T_
		}
	}
	
	public function getCode() {
		return $this->writer->getContent();
	}
}