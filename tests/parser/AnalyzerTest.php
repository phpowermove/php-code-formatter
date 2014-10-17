<?php
namespace gossi\formatter\tests\parser;

use gossi\formatter\token\Tokenizer;
use gossi\formatter\parser\Lexer;
use gossi\formatter\parser\Analyzer;
use gossi\formatter\tests\utils\SamplesTrait;
use gossi\formatter\collections\UnitCollection;
use gossi\formatter\entities\Unit;

class AnalyzerTest extends \PHPUnit_Framework_TestCase {

	use SamplesTrait;
	
	private function getBlocks($file) {
		$code = $this->getRawContent($file);
		$tokenizer = new Tokenizer();
		$tokens = $tokenizer->tokenize($code);
		
		$lexer = new Lexer();
		$tokens = $lexer->fix($tokens);
		$tokens = $lexer->filterTokens($tokens);
		
		$analyzer = new Analyzer($tokens);
		$analyzer->analyze();
		return $analyzer->getBlocks();
	}
	
	public function testBlocksOrder() {
		$this->assertBlocksOrder($this->getBlocks('class'));
	}
	
	public function testBlocksWithDocblockOrder() {
		$this->assertBlocksOrder($this->getBlocks('class-phpdoc'));
	}

	private function assertBlocksOrder(UnitCollection $blocks) {
		$this->assertEquals(Unit::UNIT_NAMESPACE, $blocks->get(0)->type);
		$this->assertEquals(Unit::UNIT_USE, $blocks->get(1)->type);
		$this->assertEquals(Unit::UNIT_TRAITS, $blocks->get(2)->type);
		$this->assertEquals(Unit::UNIT_CONSTANTS, $blocks->get(3)->type);
		$this->assertEquals(Unit::UNIT_FIELDS, $blocks->get(4)->type);
		$this->assertEquals(Unit::UNIT_METHODS, $blocks->get(5)->type);
	}
}