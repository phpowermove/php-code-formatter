<?php
namespace gossi\formatter\formatters;

use gossi\formatter\token\TokenCollection;
use gossi\formatter\config\Config;
use gossi\formatter\utils\Writer;
use gossi\formatter\token\Token;
use gossi\formatter\token\TokenVisitorInterface;
use gossi\formatter\parser\Parser;
use gossi\formatter\parser\Context;
use gossi\formatter\parser\TokenMatcher;

class BaseFormatter implements TokenVisitorInterface {
	
	/** @var Parser */
	protected $parser;
	/** @var Config */
	protected $config;
	/** @var Writer */
	protected $writer;
	/** @var Context */
	protected $context;
	/** @var TokenMatcher */
	protected $matcher;
	
	protected $nextToken;
	protected $prevToken;
	
	public function __construct(Parser $parser, Config $config, Writer $writer) {
		$this->config = $config;
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