<?php
namespace gossi\formatter\formatters;

use gossi\formatter\config\Config;
use gossi\formatter\parser\Context;
use gossi\formatter\parser\Parser;
use gossi\formatter\utils\Writer;
use phootwork\tokenizer\Token;
use phootwork\tokenizer\TokenVisitorInterface;

class DelegateFormatter implements TokenVisitorInterface {

	/** @var Config */
	protected $config;
	/** @var Writer */
	protected $writer;
	/** @var Context */
	protected $context;
	/** @var Parser */
	protected $parser;

	// formatters
	private $defaultFormatter;
	private $commentsFormatter;
	private $indentationFormatter;
	private $newlineFormatter;
	private $whitespaceFormatter;
	private $blanksFormatter;

	public function __construct(Parser $parser, Config $config) {
		$this->config = $config;
		$this->parser = $parser;
		$this->writer = new Writer([
			'indentation_character' => $config->getIndentation('character') == 'tab' ? "\t" : ' ',
			'indentation_size' => $config->getIndentation('size')
		]);

		// define rules
		$this->defaultFormatter = new DefaultFormatter($parser, $config, $this->writer);
		$this->commentsFormatter = new CommentsFormatter($parser, $config, $this->writer, $this->defaultFormatter);
		$this->indentationFormatter = new IndentationFormatter($parser, $config, $this->writer, $this->defaultFormatter);
		$this->newlineFormatter = new NewlineFormatter($parser, $config, $this->writer, $this->defaultFormatter);
		$this->whitespaceFormatter = new WhitespaceFormatter($parser, $config, $this->writer, $this->defaultFormatter);
		$this->blanksFormatter = new BlanksFormatter($parser, $config, $this->writer, $this->defaultFormatter);
	}

	public function format() {
		foreach ($this->parser->getTokens() as $token) {
			$token->accept($this);
		}
	}

	public function visitToken(Token $token) {
		$this->parser->getTracker()->visitToken($token);

		// visit all rules
		$this->commentsFormatter->visitToken($token);
		$this->indentationFormatter->visitToken($token);
		$this->newlineFormatter->visitToken($token);
		$this->whitespaceFormatter->visitToken($token);
		$this->blanksFormatter->visitToken($token);
		$this->defaultFormatter->visitToken($token);
	}

	public function getCode() {
		return $this->writer->getContent();
	}

}
