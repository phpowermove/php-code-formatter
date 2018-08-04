<?php
namespace gossi\formatter\formatters;

use gossi\code\profiles\Profile;
use gossi\formatter\parser\Parser;
use gossi\formatter\utils\Writer;

class SpecializedFormatter extends BaseFormatter {

	/** @var DefaultFormatter */
	protected $defaultFormatter;

	public function __construct(Parser $parser, Profile $profile, Writer $writer, DefaultFormatter $default) {
		parent::__construct($parser, $profile, $writer);

		$this->defaultFormatter = $default;
	}

}
