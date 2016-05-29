<?php
namespace gossi\formatter\tests\utils;

use gossi\formatter\Formatter;

trait SamplesTrait {

	protected function getContent($file) {
		return file_get_contents(sprintf(__DIR__ . '/../fixtures/samples/%s.php', $file));
	}

	protected function getRawContent($file) {
		return $this->getContent('raw/' . $file);
	}

	protected function getDefaultContent($file) {
		return $this->getContent('default/' . $file);
	}

	protected function compareSample($sample) {
		$raw = $this->getRawContent($sample);
		$formatter = new Formatter();

		// default coding style
		$code = $formatter->format($raw);
// 		if ($sample == 'class') {
// 			echo $code;
// 		}
		$this->assertEquals($this->getDefaultContent($sample), $code);

		// psr2 coding style
	}
}
