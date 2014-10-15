<?php
namespace gossi\formatter\formatters;

use gossi\formatter\token\Token;
use gossi\formatter\token\TokenCollection;
use gossi\formatter\traverse\ContextManager;
use gossi\formatter\config\Config;
use gossi\formatter\traverse\TokenTracker;
use gossi\formatter\utils\Writer;
use gossi\formatter\parser\Analyzer;

class BlanksFormatter extends AbstractSpecializedFormatter {

	private $blocks;
	private $currentBlock;
	
	public function __construct(TokenCollection $tokens, Config $config, ContextManager $context, TokenTracker $tracker, Writer $writer, DefaultFormatter $default, Analyzer $analyzer) {
		parent::__construct($tokens, $config, $context, $tracker, $writer, $default);
		
		$this->blocks = $analyzer->getBlocks();
	}
	
	protected function doVisit(Token $token) {
		$this->blockStart($token);
		$this->blockEnd($token);
	}
	
	private function blockStart(Token $token) {
		$block = $this->blocks->findBlockByStart($token);

		if ($block !== null) {
			$this->currentBlock = $block;
			echo 'found block, put blanks now for before_' . $block->type . ' on ' . $token->contents	 . ' -> ' . $this->config->getBlanks('before_' . $block->type);
			$this->blankBefore($token, $block->type);
			echo "\n";
		}
	}
	
	private function blockEnd(Token $token) {
		if ($this->currentBlock !== null && $this->currentBlock->end === $token) {
			$this->blankAfter($token, $this->currentBlock->type);
			$this->currentBlock = null;
		}
	}
	
	private function blankBefore(Token $token, $key) {
		for ($i = 0, $count = $this->config->getBlanks('before_' . $key); $i < $count; $i++) {
			$this->defaultFormatter->addPreWriteln();
		}
	}
	
	private function blankAfter(Token $token, $key) {
	for ($i = 0, $count = $this->config->getBlanks('after_' . $key); $i < $count; $i++) {
			$this->defaultFormatter->addPostWriteln();
		}
	}

}
