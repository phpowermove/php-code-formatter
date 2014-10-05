<?php

namespace gossi\formatter\visitor;

use gossi\formatter\token\Token;
use gossi\formatter\token\TokenCollection;
use gossi\formatter\utils\Writer;
use gossi\formatter\config\Config;
use gossi\formatter\token\Tokenizer;
use gossi\collection\Stack;
use gossi\formatter\traverse\ContextManager;
use gossi\formatter\traverse\TokenTracker;

class Visitor {
	
	private static $BLOCK_CONTEXT_MAPPING = [
		T_IF => 'ifelse',
		T_ELSEIF => 'ifelse',
		T_WHILE => 'while',
		T_FOREACH => 'foreach',
		T_FOR => 'for',
		T_CATCH => 'catch'
	];
	
	private $tokens;
	private $tracker;
	private $config;
	private $writer;
	private $context;
	
	// context helpers
	private $isMatchingTernary = false;
	private $isDoubleQuote = false;
	
	public function __construct(TokenCollection $tokens, Config $config) {
		$this->config = $config;
		$this->tokens = $tokens;
		$this->context = new ContextManager();
		$this->tracker = new TokenTracker($tokens, $this->context);
		$this->writer = new Writer([
			'indentation_character' => $config->getIndentation('character') == 'tab' ? "\t" : ' ',
			'indentation_size' => $config->getIndentation('size')
		]);
	}
	
	public function visit(Token $token) {
		$this->tracker->visit($token);
		$parens = $this->context->getParensContext();
		$parensReference = $this->context->getParensTokenContext();
		$structural = $this->context->getStructuralContext();
		$nextToken = $this->tracker->getNextToken();
		$prevToken = $this->tracker->getPrevToken();
		
		// whitespace, newlines and indentation
		// ------------------------------------------------
		
		// keywords
		if (in_array($token->type, Tokenizer::$KEYWORDS)) {
			$this->writer->write($token->contents . ' ');
			return;
		}

		// assignments
		if (in_array($token->contents, Tokenizer::$ASSIGNMENTS)) {
			$this->whitespaceBeforeAfter($token, 'assignment', 'assignments');
			return;
		}
		
		// operators
		if (in_array($token->contents, Tokenizer::$OPERATORS)) {
			$this->whitespaceBeforeAfter($token, 'binary', 'operators');
			return;
		}

		// prefix and postfix operators
		if ($token->type == T_INC || $token->type == T_DEC) {
			// pre
			if ($nextToken->type == T_VAR) {
				$this->whitespaceBeforeAfter($token, 'prefix', 'operators');
			}
				
			// post
			else if ($prevToken->type == T_VAR) {
				$this->whitespaceBeforeAfter($token, 'postfix', 'operators');
			}
		}
		
		// unary operator
		

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
			if ($token->contents == $contents) {
				
				// continue when semicolon and it is not tangled to a block statement
				if ($token->contents == ';' && $parens != ContextManager::LEXICAL_BLOCK) {
					continue;
				}
				
				// check context
				$context = 'default';
				
				// first check the context of the current line
				if (!empty($this->line)) {
					$context = $this->line;
				} 
				
				// is it a parenthesis group
				else if ($parens == ContextManager::LEXICAL_GROUP) {
					$context = 'grouping';
				}
				
				// a function call
				else if ($parens == ContextManager::LEXICAL_CALL) {
					$context = 'function_invocation';
				}

				// field access
				else if ($contents === '->' || $contents === '::') {
					$context = 'field_access';
				}

				// a given block statement
				else if ($parens == ContextManager::LEXICAL_BLOCK
						&& isset(self::$BLOCK_CONTEXT_MAPPING[$parensReference->type])) {
					$context = self::$BLOCK_CONTEXT_MAPPING[$parensReference->type];
				}

				$this->whitespaceBeforeAfter($token, $key, $context);

				if ($contents !== ';') {
					return;
				}
			}
		}
		
		// open curly brace
		if ($token->contents == '{') {
			
			// curly braces in strucs
			if (in_array($structural->type, Tokenizer::$STRUCTS)) {
				$this->newlineOrSpace($this->config->getBraces('struct') == 'next');
			}
			
			// curly braces in functions
			else if ($structural->type == T_FUNCTION) {
				$this->newlineOrSpace($this->config->getBraces('function') == 'next');
			}
			
			// curly braces in blocks
			if (in_array($structural->type, Tokenizer::$BLOCKS)) {
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
			if (in_array($structural->type, [T_IF, T_ELSEIF])
					&& in_array($nextToken->type, [T_ELSE, T_ELSEIF])) {
				$this->newlineOrSpace($this->config->getNewline('elseif_else'));
			}
			
			// check new line before T_CATCH
			else if ($nextToken->type == T_CATCH) {
				$this->newlineOrSpace($this->config->getNewline('catch'));
			}
			
			// check new line before finally
			else if ($token->contents == 'finally') {
				$this->newlineOrSpace($this->config->getNewline('finally'));
			}
			
			// check new line before T_CATCH
			else if ($structural->type == T_DO 
					&& $nextToken->type == T_WHILE) {
				$this->newlineOrSpace($this->config->getNewline('do_while'));
			}
			
			// anyway a new line
			else {
				$this->writer->writeln();
			}
			
			return;
		}
		
		// handling comments
		// ------------------------------------------------
		
		// multiline
		if ($token->type == T_DOC_COMMENT
				|| $token->type == T_INLINE_HTML && strpos($token->contents, '/*') !== 0) {

			$lines = explode("\n", $token->contents);
			$firstLine = array_shift($lines);
			$this->writer->writeln();
			$this->writer->writeln($firstLine);

			foreach ($lines as $line) {
				$this->writer->writeln(' ' . ltrim($line));
			}
				
			return;
		}

		// blanks
		// ------------------------------------------------
		
		
		// default behavior
		// ------------------------------------------------
		
		
		if ($token->contents == ';' && $parens != ContextManager::LEXICAL_BLOCK) {
			
			// reset line context
			$this->context->resetLineContext();
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

	private function whitespaceBeforeAfter(Token $token, $key, $context = 'default') {
		if ($this->config->getWhitespace('before_' . $key, $context)) {
			$this->writer->write(' ');
		}
	
		$this->writer->write($token->contents);
		
		if ($this->config->getWhitespace('after_' . $key, $context)) {
			$this->writer->write(' ');
		}
	}
	
	public function getCode() {
		return $this->writer->getContent();
	}
}