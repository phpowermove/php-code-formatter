<?php
namespace gossi\formatter\collections;

use gossi\formatter\entities\Unit;
use phootwork\collection\ArrayList;
use phootwork\tokenizer\Token;

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
	public function findUnitByStart(Token $token) {
		foreach ($this->collection as $unit) {
			if ($unit->start === $token) {
				return $unit;
			}
		}

		return null;
	}

}
