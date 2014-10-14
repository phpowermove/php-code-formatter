<?php
namespace gossi\formatter\parser;

use gossi\formatter\token\TokenCollection;
use gossi\formatter\token\Token;

class Lexer {

	private $tokens;
	
	public function fix(TokenCollection $tokens) {
		$fixedTokens = new TokenCollection();
		
		for ($i = 0, $n = $tokens->size(); $i < $n; $i++) {
			$token = $tokens->get($i);
		
			// fix ELSEIF
			if ($token->type == T_ELSE) {
				list($j, $nextToken) = $tokens->nextToken($i);
				if ($nextToken->type == T_IF) {
					$i = $j;
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
		$filteredTokens = new TokenCollection();
	
		for ($i = 0, $n = $tokens->size(); $i < $n; $i++) {
			$token = $tokens->get($i);

			// filter whitespace
			if ($token->type !== T_WHITESPACE) {
				$filteredTokens->add($token);
			}
		}
		return $filteredTokens;
	}
}