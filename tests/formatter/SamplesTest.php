<?php

namespace gossi\formatter\tests\formatter;

use gossi\formatter\Formatter;

class SamplesTest extends \PHPUnit_Framework_TestCase {

	private function getContent($file) {
		return file_get_contents(sprintf(__DIR__.'/../fixtures/samples/%s.php', $file));
	}
	
	private function getRawContent($file) {
		return $this->getContent('raw/' . $file);
	}
	
	private function getDefaultContent($file) {
		return $this->getContent('default/' . $file);
	}
	
	private function compareSample($sample) {
		$raw = $this->getRawContent($sample);
		$formatter = new Formatter();
		
		// java coding style
		$code = $formatter->format($raw);
		if ($sample == 'sample1') {
			echo $code;
		}
		$this->assertEquals($this->getDefaultContent($sample), $code);
		
		// psr2 coding style
		
		
		// pear coding style
	}
	
	public function testSample1() {
		$this->compareSample('sample1');
	}
	
	public function testSample2() {
// 		$this->compareSample('sample2');
	}
}