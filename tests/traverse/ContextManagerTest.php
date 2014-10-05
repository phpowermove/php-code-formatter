<?php
namespace gossi\formatter\tests\traverse;

use gossi\formatter\Formatter;
use gossi\formatter\tests\utils\SamplesTrait;
use gossi\formatter\token\Token;
use gossi\formatter\token\Tokenizer;
use gossi\formatter\traverse\ContextManager;
use gossi\formatter\traverse\TokenTracker;
use gossi\collection\ArrayList;

class ContextManagerTest extends \PHPUnit_Framework_TestCase {

	use SamplesTrait;
	
	protected function setUp() {
		$code = $this->getRawContent('sample1');
		
		$this->tokenizer = new Tokenizer();
		$this->tokens = $this->tokenizer->tokenize($code);
		$this->context = new ContextManager();
		$this->tracker = new TokenTracker($this->tokens, $this->context);
	}
	
	public function testStructuralStack() {
		$structural = new ArrayList();
		$lastStructural = '';
		
		foreach ($this->tokens as $token) {
			$this->tracker->visit($token);
			
			$structuralContext = $this->context->getStructuralContext();
			$this->assertNotNull($structuralContext);
			
			if ($structuralContext->contents !== null) {
				$structuralContext = $structuralContext->contents;

				if ($structuralContext != $lastStructural) {
					$structural->add($structuralContext);
					$lastStructural = $structuralContext;
				}
			}
		}
		
		// These tests are not ideal!
		$this->assertEquals(['if', 'else', 'for', 'while', 'if', 'while', 'for'], 
			$structural->toArray());
	}
	
	public function testParensStack() {
		$parens = new ArrayList();
		$lastParens = '';
		
		foreach ($this->tokens as $token) {
			$this->tracker->visit($token);
				
			$parensContext = $this->context->getParensContext();

			if ($parensContext !== null && $parensContext != $lastParens) {
				$parens->add($parensContext);
				$lastParens = $parensContext;
			}
		}

		// These tests are not ideal!
		$this->assertEquals(['block', 'group', 'block', 'call', 'block'],
				$parens->toArray());
	}
}