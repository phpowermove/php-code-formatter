<?php
namespace gossi\formatter\events;

use phootwork\tokenizer\Token;
use Symfony\Component\EventDispatcher\Event;

class TokenEvent extends Event {

	private $token;

	public function __construct(Token $token) {
		$this->token = $token;
	}

	public function getToken() {
		return $this->token;
	}
}
