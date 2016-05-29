<?php
namespace gossi\formatter\formatters;

use gossi\formatter\config\Config;
use gossi\formatter\parser\Parser;
use gossi\formatter\utils\Writer;

class SpecializedFormatter extends BaseFormatter {

	/** @var DefaultFormatter */
	protected $defaultFormatter;

	public function __construct(Parser $parser, Config $config, Writer $writer, DefaultFormatter $default) {
		parent::__construct($parser, $config, $writer);

		$this->defaultFormatter = $default;
	}

}
