<?php
namespace gossi\formatter\formatters;

use gossi\code\profiles\Profile;
use gossi\formatter\parser\Context;
use gossi\formatter\parser\Parser;
use gossi\formatter\parser\TokenMatcher;
use gossi\formatter\utils\Writer;
use phootwork\tokenizer\Token;
use phootwork\tokenizer\TokenVisitorInterface;

class BaseFormatter implements TokenVisitorInterface {

	/** @var Parser */
	protected $parser;
	/** @var Profile */
	protected $config;
	/** @var Writer */
	protected $writer;
	/** @var Context */
	protected $context;
	/** @var TokenMatcher */
	protected $matcher;

	protected $nextToken;
	protected $prevToken;

	public function __construct(Parser $parser, Profile $profile, Writer $writer) {
		$this->config = $profile;
		$this->writer = $writer;
		$this->parser = $parser;
		$this->context = $parser->getContext();
		$this->matcher = $parser->getMatcher();

		$this->init();
	}

	public function visitToken(Token $token) {
		$this->nextToken = $this->parser->getTracker()->getNextToken();
		$this->prevToken = $this->parser->getTracker()->getPrevToken();

		$this->doVisitToken($token);
	}

	protected function doVisitToken(Token $token) {

	}

	protected function init() {

	}

}
