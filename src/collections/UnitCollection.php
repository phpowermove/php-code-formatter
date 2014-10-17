<?php
namespace gossi\formatter\collections;

use gossi\collection\ArrayList;
use gossi\formatter\entities\Unit;

class UnitCollection extends ArrayList {
	
	
	/**
	 * Retrieves a token at the given index
	 * 
	 * @param int $index the given index
	 * @return Unit
	 */
	public function get($index) {
		return parent::get($index);
	}

	/**
	 * Searches for blocks that start with a given token and returns it or null, if none is found
	 * 
	 * @param Token $token
	 * @return Unit the found block or null
	 */
	public function findBlockByStart(Token $token) {
		foreach ($this->collection as $unit) {
			if ($unit->start === $token) {
				return $unit;
			}
		}
		
		return null;
	}

}