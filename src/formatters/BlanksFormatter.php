<?php
namespace gossi\formatter\formatters;

use phootwork\tokenizer\Token;

class BlanksFormatter extends SpecializedFormatter {

	private $units;
	private $currentUnit;

	protected function init() {
		$this->units = $this->parser->getAnalyzer()->getUnits();
	}

	protected function doVisitToken(Token $token) {
		$this->unitStart($token);
		$this->unitEnd($token);
	}

	private function unitStart(Token $token) {
		$unit = $this->units->findUnitByStart($token);

		if ($unit !== null) {
			$this->currentUnit = $unit;
			$this->blankBefore($unit->type);
		}
	}

	private function unitEnd(Token $token) {
		if ($this->currentUnit !== null && $this->currentUnit->end === $token) {
			$this->blankAfter($this->currentUnit->type);
			$this->currentUnit = null;
		}
	}

	private function blankBefore($key) {
		for ($i = 0, $count = $this->config->getBlanks('before_' . $key); $i < $count; $i++) {
			$this->defaultFormatter->addPreWriteln();
		}
	}

	private function blankAfter($key) {
	for ($i = 0, $count = $this->config->getBlanks('after_' . $key); $i < $count; $i++) {
			$this->defaultFormatter->addPostWriteln();
		}
	}

}
