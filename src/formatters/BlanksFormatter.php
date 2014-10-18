<?php
namespace gossi\formatter\formatters;

use gossi\formatter\token\Token;
use gossi\formatter\token\TokenCollection;
use gossi\formatter\config\Config;
use gossi\formatter\utils\Writer;
use gossi\formatter\parser\Analyzer;

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
			$this->blankBefore($token, $unit->type);
		}
	}
	
	private function unitEnd(Token $token) {
		if ($this->currentUnit !== null && $this->currentUnit->end === $token) {
			$this->blankAfter($token, $this->currentUnit->type);
			$this->currentUnit = null;
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
