<?php
namespace gossi\formatter\formatters;

use gossi\formatter\token\Token;
use gossi\formatter\token\TokenCollection;
use gossi\formatter\config\Config;
use gossi\formatter\traverse\ContextManager;
use gossi\formatter\traverse\TokenTracker;
use gossi\formatter\utils\Writer;
use gossi\collection\Queue;

class DefaultFormatter extends AbstractFormatter {
	
	private $preCommands;
	private $postCommands;
	private $canWriteToken = true;
	
	public function __construct(TokenCollection $tokens, Config $config, ContextManager $context, TokenTracker $tracker, Writer $writer) {
		parent::__construct($tokens, $config, $context, $tracker, $writer);

		$this->preCommands = new Queue();
		$this->postCommands = new Queue();
	}

	public function addPreWrite($content = '') {
		$this->preCommands->enqueue(['write', $content]);
	}
	
	public function addPreWriteln($content = '') {
		$this->preCommands->enqueue(['writeln', $content]);
	}
	
	public function addPreIndent() {
		$this->preCommands->enqueue(['indent']);
	}
	
	public function addPreOutdent() {
		$this->preCommands->enqueue(['outdent']);
	}
	
	public function addPostWrite($content = '') {
		$this->postCommands->enqueue(['write', $content]);
	}
	
	public function addPostWriteln($content = '') {
		$this->postCommands->enqueue(['writeln', $content]);
	}
	
	public function addPostIndent() {
		$this->postCommands->enqueue(['indent']);
	}
	
	public function addPostOutdent() {
		$this->postCommands->enqueue(['outdent']);
	}
	
	public function hideToken() {
		$this->canWriteToken = false;
	}
	
	public function doVisit(Token $token) {
		$parens = $this->context->getParensContext();
		
		// finish line on semicolon
		if ($token->contents == ';' && $parens != ContextManager::LEXICAL_BLOCK) {
			$this->context->resetLineContext();
			$this->writer->writeln($token->contents);
		} 
		
		
		// when no semicolon and token output allowed
		else if ($token->contents != ';' && $this->canWriteToken) {
			$this->processCommands($this->preCommands);
			$this->writer->write($token->contents);
			$this->processCommands($this->postCommands);
		}
		
		// reset
		$this->canWriteToken = true;
		$this->preCommands->clear();
		$this->postCommands->clear();
	}
	
	private function processCommands(Queue $commands) {
		foreach ($commands as $cmd) {
			switch ($cmd[0]) {
				case 'write':
					$this->writer->write($cmd[1]);
					break;
					
				case 'writeln':
					$this->writer->writeln($cmd[1]);
					break;
					
				case 'indent':
					$this->writer->indent();
					break;
					
				case 'outdent':
					$this->writer->outdent();
					break;
			}
		}
	}
	
}
