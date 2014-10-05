<?php
namespace gossi\formatter\tests\parser;

use gossi\formatter\Formatter;
use gossi\formatter\tests\utils\SamplesTrait;

class SamplesTest extends \PHPUnit_Framework_TestCase {

	use SamplesTrait;
	
	public function testSample1() {
		$this->compareSample('sample1');
	}
	
	public function testClass() {
// 		$this->compareSample('class-phpdoc');
	}
}