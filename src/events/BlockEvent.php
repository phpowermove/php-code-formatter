<?php
namespace gossi\formatter\events;

use gossi\formatter\entities\Block;
use phootwork\tokenizer\Token;

class BlockEvent extends TokenEvent {

	/** @var Block */
	private $block;

	public function __construct(Token $token, Block $block) {
		parent::__construct($token);
		$this->block = $block;
	}

	/**
	 * Returns the associated block
	 * 
	 * @return Block
	 */
	public function getBlock() {
		return $this->block;
	}

}
