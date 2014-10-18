<?php

namespace gossi\formatter\tests\token;

use gossi\formatter\Formatter;
use gossi\formatter\token\Token;
use gossi\formatter\token\Tokenizer;
use gossi\formatter\tests\utils\SamplesTrait;
use gossi\formatter\parser\Parser;

class TokenizerTest extends \PHPUnit_Framework_TestCase {

	use SamplesTrait;
	
	public function testTokenizer() {
		$parser = new Parser();
		$parser->parse($this->getRawContent('sample1'));
		
		$tokens = $parser->getTokens();
		$tracker = $parser->getTracker();
		
		$firstIf = $tokens->get(1);
		$firstIfOpen = $tokens->get(2);
		
		$this->assertEquals('if', $firstIf->contents);
		$this->assertEquals('(', $firstIfOpen->contents);
		
		$tracker->visitToken($firstIfOpen);
		$this->assertEquals($firstIf, $tracker->getPrevToken());
		
		$tracker->visitToken($firstIf);
		$this->assertEquals($firstIfOpen, $tracker->getNextToken());
	}
}