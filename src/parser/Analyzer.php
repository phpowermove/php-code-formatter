<?php
namespace gossi\formatter\parser;

use gossi\formatter\traverse\TokenTracker;
use gossi\formatter\traverse\ContextManager;
use gossi\formatter\token\Token;
use gossi\formatter\token\TokenCollection;
use gossi\formatter\formatters\CommentsFormatter;

use gossi\formatter\entities\Unit;
use gossi\formatter\collections\UnitCollection;

class Analyzer {

	private static $PROPERTIES = [T_PRIVATE, T_PUBLIC, T_PROTECTED, T_STATIC, T_VAR];
	private static $IDENTIFIER = [T_CONST, T_NAMESPACE, T_USE];
	private static $TYPES_MAP = [
		T_CONST => Unit::UNIT_CONSTANTS,
		T_NAMESPACE => Unit::UNIT_NAMESPACE,
		T_USE => Unit::UNIT_USE
	];

	/** @var ContextManager */
	private $context;
	/** @var TokenTracker */
	private $tracker;
	
	private $detectedBlock = null;
	private $detectedBlockType = null;
	private $currentBlock = null;
	private $blocks;

	public function __construct(TokenCollection $tokens) {
		$this->tokens = $tokens;
		$this->context = new ContextManager();
		$this->tracker = new TokenTracker($tokens, $this->context);
		$this->blocks = new UnitCollection();
	}
	
	public function getBlocks() {
		return $this->blocks;
	}

	public function analyze() {
		foreach ($this->tokens as $token) {
			$this->tracker->visit($token);
			$this->findBlockStart($token);
			$this->findBlockEnd($token);
			$this->finish($token);
		}
	}

	private function findBlockStart(Token $token) {
		$detectedBlock = null;
		$detectedBlockType = null;

		if ($this->detectedBlock === null && (in_array($token->type, self::$PROPERTIES)
				|| in_array($token->type, self::$IDENTIFIER))) {
			$detectedBlock = $token;
		}
		
		if ($detectedBlock !== null) {
			
			// traits = use statements in struct body
			if ($token->type == T_USE && $this->context->isStructBody()) {
				$detectedBlockType = Unit::UNIT_TRAITS;
			}
			
			// line statements
			else if (in_array($token->type, self::$IDENTIFIER)) {
				$detectedBlockType = self::$TYPES_MAP[$token->type];
			}
			
			// check for properties
			else if (in_array($token->type, self::$PROPERTIES)) {
				$nextToken = $token;
				do {
					$nextToken = $this->tracker->nextToken($nextToken);
					if ($nextToken !== null && $nextToken->type == T_VARIABLE) {
						$detectedBlockType = Unit::UNIT_FIELDS;
						break;
					} else if ($nextToken !== null && $nextToken->type == T_FUNCTION) {
						$detectedBlockType = Unit::UNIT_METHODS;
						break;
					}
				} while (in_array($nextToken->type, self::$PROPERTIES));
			}

			// continue last block, or start new block?
			if ($this->currentBlock !== null && $detectedBlockType === $this->currentBlock->type) {
				$prevToken = $token;
				do {
					$prevToken = $this->tracker->prevToken($prevToken); 
				} while (CommentsFormatter::isComment($prevToken));
					
				// yes, new block
				if ($prevToken !== $this->currentBlock->end) {
					$this->dumpCurrentBlock();
				}
			} else {
				$this->dumpCurrentBlock();
				$this->detectedBlock = $detectedBlock;
				$this->detectedBlockType = $detectedBlockType;
			}
		}
	}

	private function findBlockEnd(Token $token) {
		if ($this->detectedBlock !== null) {
			if ($token->contents == ';' || $token->contents == '}') {
				$this->currentBlock = new Unit();
				$this->currentBlock->start = $this->detectedBlock;
				$this->currentBlock->type = $this->detectedBlockType;
				$this->currentBlock->end = $token;

				$this->detectedBlock = null;
				$this->detectedBlockType = null;
			}
		}
	}
	
	private function finish(Token $token) {
		if ($this->tracker->isLastToken($token)) {
			$this->dumpCurrentBlock();
		}
	}
	
	private function dumpCurrentBlock() {
		if ($this->currentBlock !== null) {
			$this->blocks->add($this->currentBlock);
			$this->currentBlock = null;
		}
	}

}
