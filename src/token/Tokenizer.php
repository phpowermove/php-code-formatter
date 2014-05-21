<?php

namespace gossi\formatter\token;

class Tokenizer {
	
	const IMPORT_STATEMENTS = [T_REQUIRE, T_REQUIRE_ONCE, T_INCLUDE, T_INCLUDE_ONCE];
	
	/**
	 * Keyswords that are followed by a single space
	 */
	const KEYWORDS = [T_ABSTRACT, T_CASE, T_CLASS, T_CLONE, T_CONST, T_EXTENDS, T_FINAL, T_FINALLY, T_GLOBAL, T_IMPLEMENTS, T_INTERFACE, T_NAMESPACE, T_NEW, T_PRIVATE, T_PUBLIC, T_PROTECTED, T_THROW, T_TRAIT, T_USE];
	const BLOCKS = [T_IF, T_ELSEIF, T_ELSE, T_FOR, T_FOREACH, T_WHILE, T_DO, T_SWITCH, T_TRY, T_CATCH];
	const CASTS = [T_ARRAY_CAST, T_BOOL_CAST, T_DOUBLE_CAST, T_INT_CAST, T_OBJECT_CAST, T_STRING_CAST, T_UNSET_CAST];
	const ASSIGNMENTS = [T_AND_EQUAL, T_CONCAT_EQUAL, T_DIV_EQUAL, T_MINUS_EQUAL, T_MOD_EQUAL, T_MUL_EQUAL, T_OR_EQUAL, T_PLUS_EQUAL, T_SL_EQUAL, T_SR_EQUAL, T_XOR_EQUAL];
	const OPERATORS = [T_BOOLEAN_AND, T_BOOLEAN_OR, T_INSTANCEOF, T_IS_EQUAL, T_IS_GREATER_OR_EQUAL, T_IS_IDENTICAL, T_IS_NOT_EQUAL, T_IS_NOT_IDENTICAL, T_IS_SMALLER_OR_EQUAL, T_LOGICAL_AND, T_LOGICAL_OR, T_LOGICAL_XOR, T_SL, T_SR];

	
	public function __construct() {
		
	}

	/**
	 * 
	 * @param string $code
	 * @return TokenCollection
	 */
	public function tokenize($code) {
		$tokens = new TokenCollection();
		foreach (token_get_all($code) as $token) {
			$tokens->add(new Token($token));
		}

		$tokens = $this->getFilteredTokens($tokens);
		
		return $tokens;
	}
	
	/**
	 *
	 * @param TokenCollection $tokens
	 * @return TokenCollection
	 */
	private function getFilteredTokens($tokens) {
		$filteredTokens = new TokenCollection();
		$matchingTernary = false;

		for ($i = 0, $n = count($tokens); $i < $n; $i++) {
			$token = $tokens[$i];
			if ($token->contents == '?') {
				$matchingTernary = true;
			}
			if (in_array($token->type, self::IMPORT_STATEMENTS)) {
				list($j, $nextToken) = $this->nextToken($i, $tokens);
				
				if ($nextToken->contents == '(') {
					$filteredTokens->add($token);
					if ($tokens[$i + 1]->type != T_WHITESPACE) {
						$filteredTokens->add(new Token(array(T_WHITESPACE, ' ')));
					}
					$i = $j;
					do {
						$i++;
						$token = $tokens[$i];
						if ($token->contents != ')') {
							$filteredTokens->add($token);
						}
					} while ($token->contents != ')');
				}
			} elseif ($token->type == T_ELSE) {
				list($j, $nextToken) = $this->nextToken($i, $tokens);
				if ($nextToken->type == T_IF) {
					$i = $j;
					$filteredTokens->add(new Token(array(T_ELSEIF, 'elseif')));
				}
			} elseif ($token->contents == ':') {
				if ($matchingTernary) {
					$matchingTernary = false;
				} elseif ($tokens[$i - 1]->type == T_WHITESPACE) {
					array_pop($filteredTokens); // Remove whitespace before
				}
				$filteredTokens->add($token);
			} else {
				$filteredTokens->add($token);
			}
		}
		$tokens = $filteredTokens;
		return $tokens;
	}
	
	private function nextToken($i, $tokens) {
		do {
			$i++;
			$token = $tokens[$i];
		} while ($token->type == T_WHITESPACE);
		return [$i, $token];
	}
}