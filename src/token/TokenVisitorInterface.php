<?php
namespace gossi\formatter\token;

interface TokenVisitorInterface {
	public function visitToken(Token $token);
}