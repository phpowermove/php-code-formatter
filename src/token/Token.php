<?php

namespace gossi\formatter\token;

use gossi\formatter\visitor\Visitor;

class Token {
	
	public $type;
	public $contents;
	
	public function __construct($token) {
		if (is_array($token)) {
			$this->type = $token[0];
			$this->contents = $token[1];
		} else {
			$this->type = -1;
			$this->contents = $token;
		}
	}
	
	public function accept(Visitor $visitor) {
		return $visitor->visit($this);
	}
}