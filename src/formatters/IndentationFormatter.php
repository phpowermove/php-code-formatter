<?php
namespace gossi\formatter\formatters;

use gossi\formatter\token\Token;

class IndentationFormatter extends AbstractSpecializedFormatter {
	
	protected function doVisit(Token $token) {
		$this->indentOpenCurlyBrace($token);
		$this->indentCloseCurlyBrace($token);
	}
	
	private function indentOpenCurlyBrace(Token $token) {
		if ($token->contents == '{') {
			$this->defaultFormatter->addPostIndent();
		}
	}
	
	private function indentCloseCurlyBrace(Token $token) {
		if ($token->contents == '}') {
			$this->defaultFormatter->addPreOutdent();
		}
	}

}
