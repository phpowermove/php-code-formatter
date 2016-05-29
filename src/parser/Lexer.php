<?php
namespace gossi\formatter\parser;

use phootwork\tokenizer\Token;
use phootwork\tokenizer\TokenCollection;

/**
 * Dunno if this is a lexer...
 * 
 */
class Lexer {

	private $tokens;

	public function repair(TokenCollection $tokens) {
		$fixedTokens = new TokenCollection();

		for ($i = 0, $n = $tokens->size(); $i < $n; $i++) {
			$token = $tokens->get($i);

			// fix ELSEIF
			if ($token->type == T_ELSE) {
				$nextToken = $tokens->get($i + 1);

				if ($nextToken->type == T_IF) {
					$i++;
					$fixedTokens->add(new Token([T_ELSEIF, 'else if']));
				} else {
					$fixedTokens->add($token);
				}

				continue;
			}

			$fixedTokens->add($token);
		}
		return $fixedTokens;
	}

	/**
	 *
	 * @param TokenCollection $tokens
	 * @return TokenCollection
	 */
	public function filterTokens(TokenCollection $tokens) {
		return $tokens->filter(function (Token $token) {
			return $token->type != T_WHITESPACE;
		});
	}
}
