<?php
namespace gossi\formatter\parser;

use gossi\formatter\collections\UnitCollection;
use gossi\formatter\entities\Block;
use gossi\formatter\entities\Unit;
use gossi\formatter\events\BlockEvent;
use gossi\formatter\formatters\CommentsFormatter;
use phootwork\tokenizer\Token;
use phootwork\tokenizer\TokenCollection;

class Analyzer {

	/** @var Parser */
	private $parser;
	private $matcher;

	private $detectedUnit = null;
	private $detectedUnitType = null;
	private $currentUnit = null;
	private $units;

	public function __construct(Parser $parser) {
		$this->parser = $parser;
		$this->matcher = $parser->getMatcher();
		$this->units = new UnitCollection();

		// register listeners
		$context = $parser->getContext();
		$context->addListener(Context::EVENT_ROUTINE_LEAVE, [$this, 'onRoutineClosed']);
		$context->addListener(Context::EVENT_BLOCK_LEAVE, [$this, 'onBlockClosed']);
	}

	public function getUnits() {
		return $this->units;
	}

	public function analyze(TokenCollection $tokens) {
		foreach ($tokens as $token) {
			$this->parser->getTracker()->visitToken($token);
			$this->findUnitStart($token);
			$this->findUnitEnd($token);
			$this->finish($token);
		}
	}

	private function findUnitStart(Token $token) {
		$detectedUnit = null;
		$detectedUnitType = null;

		if ($this->detectedUnit === null && ($this->matcher->isModifier($token)
				|| $this->matcher->isUnitIdentifier($token))) {
			$detectedUnit = $token;
		}

		if ($detectedUnit !== null) {

			// traits = use statements in struct body
			if ($token->type == T_USE && $this->parser->getContext()->getCurrentContext() == Context::CONTEXT_STRUCT) {
				$detectedUnitType = Unit::UNIT_TRAITS;
			}

			// line statements
			else if ($this->matcher->isUnitIdentifier($token)) {
				$detectedUnitType = Unit::getType($token);
			}

			// check for properties
			else if ($this->matcher->isModifier($token)) {
				$nextToken = $token;
				do {
					$nextToken = $this->parser->getTracker()->nextToken($nextToken);
					if ($nextToken !== null && $nextToken->type == T_VARIABLE) {
						$detectedUnitType = Unit::UNIT_FIELDS;
						break;
					} else if ($nextToken !== null && $nextToken->type == T_FUNCTION) {
						$detectedUnitType = Unit::UNIT_METHODS;
						break;
					} else if ($nextToken !== null && $nextToken->type == T_CLASS) {
						return;
					}
				} while ($this->matcher->isModifier($nextToken));
			}

			// continue last unit, or start new unit?
			if ($detectedUnitType !== Unit::UNIT_METHODS
					&& $this->currentUnit !== null
					&& $detectedUnitType === $this->currentUnit->type) {
				$prevToken = $token;
				do {
					$prevToken = $this->parser->getTracker()->prevToken($prevToken);
				} while (CommentsFormatter::isComment($prevToken)
						|| $this->matcher->isModifier($prevToken));

				// yes, new unit
				if ($prevToken !== $this->currentUnit->end) {
					$this->dumpCurrentUnit();
					$this->detectedUnit = $detectedUnit;
					$this->detectedUnitType = $detectedUnitType;
				}
			} else {
				$this->dumpCurrentUnit();
				$this->detectedUnit = $detectedUnit;
				$this->detectedUnitType = $detectedUnitType;
			}
		}
	}

	private function findUnitEnd(Token $token) {
		if ($this->detectedUnit !== null) {
			if ($token->contents == ';') {
				$this->flushDetection($token);
			}
		}
	}

	private function flushDetection(Token $token) {
		$this->currentUnit = new Unit();
		$this->currentUnit->start = $this->detectedUnit;
		$this->currentUnit->type = $this->detectedUnitType;
		$this->currentUnit->end = $token;

		$this->detectedUnit = null;
		$this->detectedUnitType = null;
	}

	public function onRoutineClosed(BlockEvent $event) {
		if ($this->parser->getContext()->getCurrentContext() == Context::CONTEXT_STRUCT) {

			$block = $event->getBlock();

			if ($this->currentUnit === null || $block->start != $this->currentUnit->start) {
				$this->detectedUnit = $block->start;
				$this->detectedUnitType = Unit::UNIT_METHODS;
				$this->flushDetection($event->getToken());
			}

			$this->dumpCurrentUnit();
		}
	}

	public function onBlockClosed(BlockEvent $event) {
		$block = $event->getBlock();
		if ($block->type == Block::TYPE_USE || $block->type == Block::TYPE_NAMESPACE) {
			$this->detectedUnit = $block->start;
			$this->detectedUnitType = $block->type;
			$this->flushDetection($event->getToken());
		}
	}

	private function finish(Token $token) {
		if ($this->parser->getTracker()->isLastToken($token)) {
			$this->dumpCurrentUnit();
		}
	}

	private function dumpCurrentUnit() {
		if ($this->currentUnit !== null) {
			$this->units->add($this->currentUnit);
			$this->currentUnit = null;
		}
	}

}
