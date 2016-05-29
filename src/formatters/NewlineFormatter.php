<?php
namespace gossi\formatter\formatters;

use gossi\formatter\entities\Block;
use gossi\formatter\events\BlockEvent;
use gossi\formatter\parser\Context;

class NewlineFormatter extends SpecializedFormatter {

	protected function init() {
		$this->context->addListener(Context::EVENT_BLOCK_ENTER, [$this, 'preOpenCurlyBrace']);
		$this->context->addListener(Context::EVENT_BLOCK_LEAVE, [$this, 'postCloseCurlyBrace']);
	}

	public function preOpenCurlyBrace(BlockEvent $event) {
		$block = $event->getBlock();

		// curly braces in strucs
		if ($block->isStruct()) {
			$this->newlineOrSpaceBeforeCurly($this->config->getBraces('struct') == 'next');
		}

		// curly braces in functions
		else if ($block->isRoutine()) {
			$this->newlineOrSpaceBeforeCurly($this->config->getBraces('function') == 'next');
		}

		// curly braces in blocks
		else if ($block->isBlock()) {
			$this->newlineOrSpaceBeforeCurly($this->config->getBraces('blocks') == 'next');
		}

		// new line after open curly brace
		$this->defaultFormatter->addPostWriteln();
	}

	public function postCloseCurlyBrace(BlockEvent $event) {
		$block = $event->getBlock();
		$token = $event->getToken();
		$nextToken = $this->parser->getTracker()->nextToken($token);

		// check new line before T_ELSE and T_ELSEIF
		if (in_array($block->type, [Block::TYPE_IF, Block::TYPE_ELSEIF])
				&& in_array($nextToken->type, [T_ELSE, T_ELSEIF])) {
			$this->newlineOrSpaceAfterCurly($this->config->getNewline('elseif_else'));
		}

		// check new line before T_CATCH
		else if ($this->nextToken->type == T_CATCH) {
			$this->newlineOrSpaceAfterCurly($this->config->getNewline('catch'));
		}

		// check new line before finally
		else if ($token->contents == 'finally') {
			$this->newlineOrSpaceAfterCurly($this->config->getNewline('finally'));
		}

		// check new line before while in a do-while block
		else if ($block->type == Block::TYPE_DO && $nextToken->type == T_WHILE) {
			$this->newlineOrSpaceAfterCurly($this->config->getNewline('do_while'));
		}

		// anyway a new line
		else {
			$this->defaultFormatter->addPostWriteln();
		}
	}

	private function newlineOrSpaceBeforeCurly($condition) {
		if ($condition) {
			$this->writer->writeln();
		} else if ($this->config->getWhitespace('before_curly')) {
			$this->writer->write(' ');
		}
	}

	private function newlineOrSpaceAfterCurly($condition) {
		if ($condition) {
			$this->writer->writeln();
		} else {
			$this->defaultFormatter->addPostWrite(' ');
		}
	}
}
