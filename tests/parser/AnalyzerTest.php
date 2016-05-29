<?php
namespace gossi\formatter\tests\parser;

use gossi\formatter\collections\UnitCollection;
use gossi\formatter\entities\Unit;
use gossi\formatter\parser\Parser;
use gossi\formatter\tests\utils\SamplesTrait;

class AnalyzerTest extends \PHPUnit_Framework_TestCase {

	use SamplesTrait;

	private function getParser($file) {
		$code = $this->getRawContent($file);
		$parser = new Parser();
		$parser->parse($code);
		return $parser;
	}

	private function getUnits($file) {
		return $this->getParser($file)->getAnalyzer()->getUnits();
	}

	public function testUnitsOrder() {
		$this->assertUnitsOrder($this->getUnits('class'));
	}

	public function testUnitsWithDocblockOrder() {
		$this->assertUnitsOrder($this->getUnits('class-phpdoc'));
	}

	private function assertUnitsOrder(UnitCollection $units) {
		$this->assertEquals(Unit::UNIT_NAMESPACE, $units->get(0)->type);
		$this->assertEquals(Unit::UNIT_USE, $units->get(1)->type);
		$this->assertEquals(Unit::UNIT_TRAITS, $units->get(2)->type);
		$this->assertEquals(Unit::UNIT_CONSTANTS, $units->get(3)->type);
		$this->assertEquals(Unit::UNIT_FIELDS, $units->get(4)->type);
		$this->assertEquals(Unit::UNIT_METHODS, $units->get(5)->type);
	}

	public function testUnitsAbstractOrder() {
		$units = $this->getUnits('abstract-class');

		$this->assertEquals(Unit::UNIT_NAMESPACE, $units->get(0)->type);
		$this->assertEquals(Unit::UNIT_FIELDS, $units->get(1)->type);
		$this->assertEquals(Unit::UNIT_METHODS, $units->get(2)->type);
		$this->assertEquals(Unit::UNIT_METHODS, $units->get(3)->type);
		$this->assertEquals(Unit::UNIT_METHODS, $units->get(4)->type);
		$this->assertEquals(Unit::UNIT_METHODS, $units->get(5)->type);
		$this->assertEquals(Unit::UNIT_CONSTANTS, $units->get(6)->type);
	}

// 	public function testUnitsOrderPosition() {

// 	}
}
