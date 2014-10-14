<?php
namespace gossi\formatter\formatters;

use gossi\formatter\token\TokenCollection;
use gossi\formatter\config\Config;
use gossi\formatter\traverse\ContextManager;
use gossi\formatter\traverse\TokenTracker;
use gossi\formatter\utils\Writer;
use gossi\formatter\token\Token;
use gossi\formatter\token\TokenVisitorInterface;

class DelegateFormatter implements TokenVisitorInterface {
	
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
	
	private $commentsFormatter;
	private $indentationFormatter;
	private $newlineFormatter;
	private $whitespaceFormatter;
	private $defaultFormatter;
	
	public function __construct(TokenCollection $tokens, Config $config) {
		$this->config = $config;
		$this->tokens = $tokens;
		$this->context = new ContextManager();
		$this->tracker = new TokenTracker($tokens, $this->context);
		$this->writer = new Writer([
			'indentation_character' => $config->getIndentation('character') == 'tab' ? "\t" : ' ',
			'indentation_size' => $config->getIndentation('size')
		]);

		// define rules
		$this->defaultFormatter = new DefaultFormatter($tokens, $config, $this->context, $this->tracker, $this->writer);
		$this->commentsFormatter = new CommentsFormatter($tokens, $config, $this->context, $this->tracker, $this->writer, $this->defaultFormatter);
		$this->indentationFormatter = new IndentationFormatter($tokens, $config, $this->context, $this->tracker, $this->writer, $this->defaultFormatter);
		$this->newlineFormatter = new NewlineFormatter($tokens, $config, $this->context, $this->tracker, $this->writer, $this->defaultFormatter);
		$this->whitespaceFormatter = new WhitespaceFormatter($tokens, $config, $this->context, $this->tracker, $this->writer, $this->defaultFormatter);
	}
	
	public function visit(Token $token) {
		$this->tracker->visit($token);
		
		// visit all rules
		$this->commentsFormatter->visit($token);
		$this->indentationFormatter->visit($token);
		$this->newlineFormatter->visit($token);
		$this->whitespaceFormatter->visit($token);
		$this->defaultFormatter->visit($token);
	}
	
	public function getCode() {
		return $this->writer->getContent();
	}

}