<?php
namespace gossi\formatter\events;

use Symfony\Component\EventDispatcher\Event;
use phootwork\tokenizer\Token;


class TokenEvent extends Event {

	private $token;
	
	public function __construct(Token $token) {
		$this->token = $token;
	}
	
	public function getToken() {
		return $this->token;
	}
}
