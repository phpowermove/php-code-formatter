<?php
namespace gossi\formatter;

use gossi\code\profiles\Profile;
use gossi\formatter\formatters\DelegateFormatter;
use gossi\formatter\parser\Parser;

class Formatter {

	/** @var Profile */
	private $profile;

	public function __construct($profile = null) {
		$this->profile = new Profile($profile);
	}

	public function format($code) {
		$parser = new Parser();
		$parser->parse($code);

		// formatting
		$delegate = new DelegateFormatter($parser, $this->profile);
		$delegate->format();

		// post processing

		return $delegate->getCode();
	}
}
