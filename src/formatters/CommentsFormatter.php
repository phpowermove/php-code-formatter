<?php
namespace gossi\formatter\formatters;

use phootwork\tokenizer\Token;

class CommentsFormatter extends SpecializedFormatter {

	protected function doVisitToken(Token $token) {
		// multiline
		if ($token->type == T_DOC_COMMENT
				|| $token->type == T_INLINE_HTML && strpos($token->contents, '/*') !== 0) {

			$lines = explode("\n", $token->contents);
			$firstLine = array_shift($lines);
			$this->writer->writeln();
			$this->writer->writeln($firstLine);

			foreach ($lines as $line) {
				$this->writer->writeln(' ' . ltrim($line));
			}

			$this->defaultFormatter->hideToken();
		}
	}

	public static function isComment(Token $token) {
		return $token->type == T_DOC_COMMENT
				|| ($token->type == T_INLINE_HTML && strpos($token->contents, '/*') !== 0)
				|| $token->type == T_COMMENT;
	}
}
