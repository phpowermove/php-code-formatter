<?php
namespace gossi\formatter\token;

interface TokenVisitorInterface {
	public function visit(Token $token);
}