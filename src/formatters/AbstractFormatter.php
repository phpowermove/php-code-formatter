<?php
namespace gossi\formatter\formatters;

use gossi\formatter\token\TokenCollection;
use gossi\formatter\config\Config;
use gossi\formatter\traverse\ContextManager;
use gossi\formatter\traverse\TokenTracker;
use gossi\formatter\utils\Writer;
use gossi\formatter\token\Token;
use gossi\formatter\token\TokenVisitor;

abstract class AbstractFormatter implements TokenVisitor {
	
	/** @var TokenCollection */
	protected $tokens;
	/** @var TokenTracker */
	protected $tracker;
	/** @var Config */
	protected $config;
	/** @var Writer */
	protected $writer;
	/** @var ContextManager */
	protected $context;
	
	protected $nextToken;
	protected $prevToken;
	
	public function __construct(TokenCollection $tokens, Config $config, ContextManager $context, TokenTracker $tracker, Writer $writer) {
		$this->config = $config;
		$this->tokens = $tokens;
		$this->context = $context;
		$this->tracker = $tracker;
		$this->writer = $writer;
	}
	
	public function visit(Token $token) {
// 		$parens = $this->context->getParensContext();
// 		$parensReference = $this->context->getParensReferenceContext();
// 		$structural = $this->context->getStructuralContext();
		$this->nextToken = $this->tracker->getNextToken();
		$this->prevToken = $this->tracker->getPrevToken();
		
		$this->doVisit($token);
	}
	
	abstract protected function doVisit(Token $token);

}