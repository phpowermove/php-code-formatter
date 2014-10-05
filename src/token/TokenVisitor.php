<?php
namespace gossi\formatter\token;

interface TokenVisitor {
	public function visit(Token $token);
}