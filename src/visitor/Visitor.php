<?php

namespace gossi\formatter\visitor;

use gossi\formatter\token\Token;
use gossi\formatter\token\TokenCollection;
use gossi\formatter\utils\Writer;
use gossi\formatter\config\Config;
use gossi\formatter\token\Tokenizer;

class Visitor {
	
	const LEXICAL_BLOCK = 'block';
	const LEXICAL_CALL = 'call';
	
	private $tokens;
	private $config;
	private $writer;
	
	// contexts
	private $structural = [];
	private $lexical = [];
	
	// context helpers
	private $structuralDetected;
	private $isMatchingTernary = false;
	private $isDoubleQuote = false;
	
	public function __construct(TokenCollection $tokens, Config $config) {
		$this->tokens = $tokens;
		$this->config = $config;
		$this->writer = new Writer([
			'indentation_character' => $config->getIndentation('character') == 'tab' ? "\t" : ' ',
			'indentation_size' => $config->getIndentation('size')
		]);
	}
	
	public function visit(Token $token) {
// 		$i = $this->tokens->indexOf($token);
		$lexical = $this->peekLexical();
		$structural = $this->peekStructural();
		$nextToken = $this->nextToken($token);
		$prevToken = $this->prevToken($token);

		// controlling context
		// ------------------------------------------------
		
		// detect structural context
		if (in_array($token->type, Tokenizer::$BLOCKS) 
				|| in_array($token->type, Tokenizer::$STRUCTURAL)) {
			$this->structuralDetected = $token;
		}
		
		// push structural context when entering
		if ($token->contents == '{') {
			$this->structural[] = $this->structuralDetected;
			$structural = $this->structuralDetected;
		}
		
		// popping structural context
		if ($token->contents == '}') {
			$structural = array_pop($this->structural);
		}
		
		// lexical context
		if ($token->contents == '(') {
			if ($prevToken && (in_array($prevToken->type, Tokenizer::$BLOCKS)
					|| in_array($prevToken->type, Tokenizer::$OPERATORS))) {
				$lexical = self::LEXICAL_BLOCK;
			} else {
				$lexical = self::LEXICAL_CALL;
			}
			$this->lexical[] = $lexical;
		} else if ($token->contents == ')') {
			$lexical = array_pop($this->lexical);
		}
		
		// whitespace, newlines and indentation
		// ------------------------------------------------
		
		// keywords
		if (in_array($token->type, Tokenizer::$KEYWORDS)) {
			$this->writer->write($token->contents . ' ');
			return;
		}

		// operators and assignments
		if (in_array($token->contents, Tokenizer::$ASSIGNMENTS)
				|| in_array($token->contents, Tokenizer::$OPERATORS)) {
			$this->whitespaceBeforeAfter($token, 'binary');
			return;
		}

		// syntax
		$beforeAfter = [
			')' => 'close',
			'(' => 'open',
			',' => 'comma', 
			';' => 'semicolon',
			':' => 'colon',
			'=>' => 'arrow',
			'->' => 'arrow', // function invocation
			'::' => 'doublecolon', // function invocation
			'?' => 'questionmark'
		];
		
		foreach ($beforeAfter as $contents => $key) {
			if ($token->contents == $contents
					&& ($token->contents == ';' ? $lexical == self::LEXICAL_BLOCK : true)
				) {
				$this->whitespaceBeforeAfter($token, $key);
				
				if ($contents != ';') {
					return;
				}
			}
		}
		
		// open curly brace
		if ($token->contents == '{') {
			
			// curly braces in strucs
			if ($structural && in_array($structural->type, Tokenizer::$STRUCTS)) {
				$this->newlineOrSpace($this->config->getBraces('struct') == 'next');
			}
			
			// curly braces in functions
			else if ($structural && $structural->type == T_FUNCTION) {
				$this->newlineOrSpace($this->config->getBraces('function') == 'next');
			}
			
			// curly braces in blocks
			if ($structural && in_array($structural->type, Tokenizer::$BLOCKS)) {
				$this->newlineOrSpace($this->config->getBraces('blocks') == 'next');
			}
			
			$this->writer->writeln('{');
			$this->writer->indent();
			return;
		}
		
		// close curly brace
		if ($token->contents == '}') {
			
			$this->writer->outdent();
			$this->writer->write('}');
			
			// check new line before T_ELSE and T_ELSEIF
			if ($structural && in_array($structural->type, [T_IF, T_ELSEIF])
					&& $nextToken && in_array($nextToken->type, [T_ELSE, T_ELSEIF])) {
				$this->newlineOrSpace($this->config->getNewlines('elseif_else'));
			}
			
			// check new line before T_CATCH
			else if ($nextToken && $nextToken->type == T_CATCH) {
				$this->newlineOrSpace($this->config->getNewlines('catch'));
			}
			
			// check new line before finally
			else if ($token->contents == 'finally') {
				$this->newlineOrSpace($this->config->getNewlines('finally'));
			}
			
			// check new line before T_CATCH
			else if ($structural && $structural->type == T_DO 
					&& $nextToken && $nextToken->type == T_WHILE) {
				$this->newlineOrSpace($this->config->getNewlines('do_while'));
			}
			
			// anyway a new line
			else {
				$this->writer->writeln();
			}
			
			return;
		}
		
		// default behavior
		// ------------------------------------------------
		
		
		if ($token->contents == ';' && $lexical != self::LEXICAL_BLOCK) {
			$this->writer->writeln($token->contents);
		} else if ($token->contents != ';') {
			$this->writer->write($token->contents);
		}
	}
	
	private function newlineOrSpace($condition) {
		if ($condition) {
			$this->writer->writeln();
		} else if ($this->config->getWhitespace('before_curly')) {
			$this->writer->write(' ');
		}
	}

	private function whitespaceBeforeAfter(Token $token, $key) {
		if ($this->config->getWhitespace('before_' . $key)) {
			$this->writer->write(' ');
		}
	
		$this->writer->write($token->contents);
		
		if ($this->config->getWhitespace('after_' . $key)) {
			$this->writer->write(' ');
		}
	}
	
	private function peekStructural() {
		$size = count($this->structural);
		if ($size > 0) {
			return $this->structural[$size - 1];
		}
	}
	
	private function peekLexical() {
		$size = count($this->lexical);
		if ($size > 0) {
			return $this->lexical[$size - 1];
		}
	}
	
	private function nextToken($token, $offset = 1) {
		$index = $this->tokens->indexOf($token);
		return $this->tokens->get($index + $offset);
	}
	
	private function prevToken($token, $offset = 1) {
		$index = $this->tokens->indexOf($token);
		return $this->tokens->get($index - $offset);
	}
	
	public function getCode() {
		return $this->writer->getContent();
	}
}