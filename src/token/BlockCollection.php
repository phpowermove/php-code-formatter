<?php

namespace gossi\formatter\token;

use gossi\collection\ArrayList;

class BlockCollection extends ArrayList {
	
	
	/**
	 * Retrieves a token at the given index
	 * 
	 * @param int $index the given index
	 * @return Block
	 */
	public function get($index) {
		return parent::get($index);
	}

	/**
	 * Searches for blocks that start with a given token and returns it or null, if none is found
	 * 
	 * @param Token $token
	 * @return Block the found block or null
	 */
	public function findBlockByStart(Token $token) {
		foreach ($this->collection as $block) {
			if ($block->start === $token) {
				return $block;
			}
		}
		
		return null;
	}

}