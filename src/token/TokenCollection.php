<?php

namespace gossi\formatter\token;

use gossi\collection\ArrayList;

class TokenCollection extends ArrayList {
	
	
	/**
	 * Retrieves a token at the given index
	 * 
	 * @param int $index the given index
	 * @return Token 
	 */
	public function get($index) {
		return parent::get($index);
	}

	/**
	 * Returns the next non-whitespace token
	 * 
	 * @param int $index
	 * @return array [index, token]
	 */
	public function nextToken($index) {
		$size = count($this->collection);
		if ($index >= $size - 1) {
			return [$index + 1, null];
		}

		do {
			$index++;
			$token = $this->collection[$index];
		} while ($token->type == T_WHITESPACE && $index < $size);

		return [$index, $token];
	}
	
	/**
	 * Returns the previous non-whitespace token
	 *
	 * @param int $index
	 * @return array [index, token]
	 */
	public function prevToken($index) {
		if ($index < 1) {
			return [-1, null];
		}

		do {
			$index--;
			$token = $this->collection[$index];
		} while ($token->type == T_WHITESPACE && $index >= 0);
	
		return [$index, $token];
	}

}