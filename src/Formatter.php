<?php
namespace gossi\formatter;

use gossi\code\profiles\Profile;
use gossi\formatter\formatters\DelegateFormatter;
use gossi\formatter\parser\Parser;

class Formatter {

	/** @var Profile */
	private $profile;

	public function __construct($profile = null) {
		if (is_string($profile) || $profile === null) {
			$profile = new Profile($profile);
		}
		if (!($profile instanceof Profile)) {
			throw new \InvalidArgumentException('$profile must be a string or instanceof gossi\code\profiles\Profile');
		}
		$this->profile = $profile;
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
