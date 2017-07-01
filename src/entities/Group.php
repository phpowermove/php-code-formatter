<?php
namespace gossi\formatter\entities;

use phootwork\tokenizer\Token;

class Group {

	const BLOCK = 'block';
	const CALL = 'call';
	const GROUP = 'group';

	/** @var Token */
	public $start = null;

	/** @var Token */
	public $end = null;

	/** @var Token */
	public $token = null;

	public $type = '';

	public function isBlock() {
		return $this->type == self::BLOCK;
	}

	public function isCall() {
		return $this->type == self::CALL;
	}

	public function isGroup() {
		return $this->type == self::GROUP;
	}
}
