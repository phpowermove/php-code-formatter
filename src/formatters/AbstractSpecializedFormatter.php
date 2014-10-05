<?php
namespace gossi\formatter\formatters;

use gossi\formatter\token\TokenCollection;
use gossi\formatter\config\Config;
use gossi\formatter\traverse\ContextManager;
use gossi\formatter\traverse\TokenTracker;
use gossi\formatter\utils\Writer;

abstract class AbstractSpecializedFormatter extends AbstractFormatter {
	
	/** @var DefaultFormatter */
	protected $defaultFormatter;
	
	public function __construct(TokenCollection $tokens, Config $config, ContextManager $context, TokenTracker $tracker, Writer $writer, DefaultFormatter $default) {
		parent::__construct($tokens, $config, $context, $tracker, $writer);
		
		$this->defaultFormatter = $default;
	}
	
}