<?php

namespace gossi\formatter\tests\formatter;


use gossi\formatter\Formatter;
class SamplesTest extends \PHPUnit_Framework_TestCase {

	private function getContent($file) {
		return file_get_contents(sprintf(__DIR__.'/../fixtures/samples/%s.php', $file));
	}
	
	private function compareSample($sample) {
		$formatter = new Formatter();
		$code = $formatter->format($this->getContent('before/sample1'));
		
		echo $code;
// 		$this->assertEquals($this->getContent('after/sample1'), $code);
	}
	
	public function testSample1() {
		$this->compareSample('sample1');
	}
}