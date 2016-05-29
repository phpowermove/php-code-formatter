<?php
namespace gossi\formatter\formatters;

use phootwork\tokenizer\Token;

class IndentationFormatter extends SpecializedFormatter {

	protected function doVisitToken(Token $token) {
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
