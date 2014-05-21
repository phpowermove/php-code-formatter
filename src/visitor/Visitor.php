<?php

namespace gossi\formatter\visitor;

use gossi\formatter\token\Token;
use gossi\formatter\token\TokenCollection;
use gossi\formatter\utils\Writer;

class Visitor {
	
	private $tokens;
	private $options;
	private $writer;
	private $context = [];
	
	public function __construct(TokenCollection $tokens, $options) {
		$this->tokens = $tokens;
		$this->options = $options;
		$this->writer = new Writer();
	}
	
	public function visit(Token $token) {
		switch ($token->type) {
// 			case T_
		}
	}
	
	public function getCode() {
		return $this->writer->getContent();
	}
}