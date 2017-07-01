<?php
namespace gossi\formatter\formatters;

use gossi\formatter\entities\Group;
use phootwork\collection\Queue;
use phootwork\tokenizer\Token;

class DefaultFormatter extends BaseFormatter {

	private $preCommands;
	private $postCommands;
	private $showToken = true;

	protected function init() {
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
		$this->showToken = false;
	}

	protected function doVisitToken(Token $token) {
		$group = $this->context->getGroupContext();

		// pre commands
		$this->processCommands($this->preCommands);

		// finish line on semicolon
		if ($token->contents == ';' && $group->type != Group::BLOCK) {
			$this->context->resetLineContext();
			$this->writer->writeln($token->contents);
		}

		// when no semicolon and token output allowed
		else if ($this->showToken) {
			$this->writer->write($token->contents);
		}

		// post commands
		$this->processCommands($this->postCommands);

		// reset
		$this->preCommands->clear();
		$this->postCommands->clear();
		$this->showToken = true;
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
