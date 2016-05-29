<?php
namespace gossi\formatter;

use gossi\formatter\config\Config;
use gossi\formatter\formatters\DelegateFormatter;
use gossi\formatter\parser\Parser;

class Formatter {

	private $config;

	public function __construct($profile = null) {
		$this->config = new Config($profile);
	}

	public function format($code) {
		$parser = new Parser();
		$parser->parse($code);

		// formatting
		$delegate = new DelegateFormatter($parser, $this->config);
		$delegate->format();

		// post processing
		
		return $delegate->getCode();
	}
}
