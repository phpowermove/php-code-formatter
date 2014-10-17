<?php

namespace gossi\formatter\tests\token;

use gossi\formatter\Formatter;
use gossi\formatter\token\Token;
use gossi\formatter\token\Tokenizer;
use gossi\formatter\traverse\TokenTracker;
use gossi\formatter\traverse\ContextManager;

class TokenizerTest extends \PHPUnit_Framework_TestCase {

	protected $tokens;
	protected $context;
	protected $tracker;
	
	protected function setUp() {
		$code = file_get_contents(__DIR__.'/../fixtures/samples/raw/sample1.php');

		$tokenizer = new Tokenizer();
		$this->tokens = $tokenizer->tokenize($code);
		
		$this->context = new ContextManager();
		$this->tracker = new TokenTracker($this->tokens, $this->context);
	}
	
	public function testTokenizer() {
		$firstIf = $this->tokens->get(1);
		$firstIfOpen = $this->tokens->get(2);
		
		$this->assertEquals('if', $firstIf->contents);
		$this->assertEquals('(', $firstIfOpen->contents);
		
		$this->tracker->visit($firstIfOpen);
		$this->assertEquals($firstIf, $this->tracker->getPrevToken());
		
		$this->tracker->visit($firstIf);
		$this->assertEquals($firstIfOpen, $this->tracker->getNextToken());
	}
}