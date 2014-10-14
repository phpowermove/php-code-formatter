<?php
namespace gossi\formatter\tests\parser;

use gossi\formatter\token\Tokenizer;
use gossi\formatter\parser\Lexer;
use gossi\formatter\parser\Analyzer;
use gossi\formatter\tests\utils\SamplesTrait;
use gossi\formatter\token\Block;
use gossi\formatter\token\BlockCollection;

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
	
	public function testBlocks() {
		$this->assertBlocks($this->getBlocks('class'));
	}
	
	public function testBlocksWithDocblock() {
		$this->assertBlocks($this->getBlocks('class-phpdoc'));
	}

	private function assertBlocks(BlockCollection $blocks) {
		$this->assertEquals(Block::BLOCK_NAMESPACE, $blocks->get(0)->type);
		$this->assertEquals(Block::BLOCK_USE, $blocks->get(1)->type);
		$this->assertEquals(Block::BLOCK_TRAITS, $blocks->get(2)->type);
		$this->assertEquals(Block::BLOCK_CONSTANTS, $blocks->get(3)->type);
		$this->assertEquals(Block::BLOCK_FIELDS, $blocks->get(4)->type);
		$this->assertEquals(Block::BLOCK_METHODS, $blocks->get(5)->type);
	}
}