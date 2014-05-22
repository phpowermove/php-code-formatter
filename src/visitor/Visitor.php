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
	const LEXICAL_GROUP = 'group';
	
	private static $BLOCK_CONTEXT_MAPPING = [
		T_IF => 'ifelse',
		T_ELSEIF => 'ifelse',
		T_WHILE => 'while',
		T_FOREACH => 'foreach',
		T_FOR => 'for',
		T_CATCH => 'catch'
	];
	
	private $tokens;
	private $config;
	private $writer;
	
	// contexts
	private $structural = [];
	private $parens = [];
	private $parensReference = [];
	private $line;
	
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
		$parens = $this->peekParens();
		$parensReference = $this->peekParensReference();
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
		
		// parens context
		if ($token->contents == '(') {
			if (in_array($prevToken->type, Tokenizer::$BLOCKS)
					|| in_array($prevToken->type, Tokenizer::$OPERATORS)) {
				$parens = self::LEXICAL_BLOCK;
				$parensReference = $prevToken;
			} else if ($this->isFunctionInvocation($token)) {
				$parens = self::LEXICAL_CALL;
				$parensReference = $prevToken;
			} else {
				$parens = self::LEXICAL_GROUP;
				$parensReference = new Token();
			}

			$this->parens[] = $parens;
			$this->parensReference[] = $parensReference;
		} else if ($token->contents == ')') {
			$parens = array_pop($this->parens);
			$parensReference = array_pop($this->parensReference);
		}
		
		// line context
		if (in_array($token->contents, Tokenizer::$LINE_CONTEXT)) {
			$this->line = $token->contents;
		}
		
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
				if ($token->contents == ';' && $parens != self::LEXICAL_BLOCK) {
					continue;
				}
				
				// check context
				$context = 'default';
				
				// first check the context of the current line
				if (!empty($this->line)) {
					$context = $this->line;
				} 
				
				// is it a parenthesis group
				else if ($parens == self::LEXICAL_GROUP) {
					$context = 'grouping';
				}
				
				// a function call
				else if ($parens == self::LEXICAL_CALL) {
					$context = 'function_invocation';
				}

				// field access
				else if ($contents === '->' || $contents === '::') {
					$context = 'field_access';
				}

				// a given block statement
				else if ($parens == self::LEXICAL_BLOCK
						&& isset(self::$BLOCK_CONTEXT_MAPPING[$parensReference->type])) {
					$context = self::$BLOCK_CONTEXT_MAPPING[$parensReference->type];
				}

				$this->whitespaceBeforeAfter($token, $key, $context);

				if ($contents != ';') {
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
				$this->newlineOrSpace($this->config->getNewlines('elseif_else'));
			}
			
			// check new line before T_CATCH
			else if ($nextToken->type == T_CATCH) {
				$this->newlineOrSpace($this->config->getNewlines('catch'));
			}
			
			// check new line before finally
			else if ($token->contents == 'finally') {
				$this->newlineOrSpace($this->config->getNewlines('finally'));
			}
			
			// check new line before T_CATCH
			else if ($structural->type == T_DO 
					&& $nextToken->type == T_WHILE) {
				$this->newlineOrSpace($this->config->getNewlines('do_while'));
			}
			
			// anyway a new line
			else {
				$this->writer->writeln();
			}
			
			return;
		}

		// blanks
		// ------------------------------------------------
		
		// default behavior
		// ------------------------------------------------
		
		
		if ($token->contents == ';' && $parens != self::LEXICAL_BLOCK) {
			
			// reset line context
			$this->line = null;
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
	
	private function peekStructural() {
		$size = count($this->structural);
		if ($size > 0) {
			return $this->structural[$size - 1];
		}
		return new Token();
	}
	
	private function peekParens() {
		$size = count($this->parens);
		if ($size > 0) {
			return $this->parens[$size - 1];
		}
		return new Token();
	}
	
	private function peekParensReference() {
		$size = count($this->parensReference);
		if ($size > 0) {
			return $this->parensReference[$size - 1];
		}
		return new Token();
	}
	
	private function nextToken($token, $offset = 1) {
		$index = $this->tokens->indexOf($token);
		$t = $this->tokens->get($index + $offset);
		if (empty($t)) {
			$t = new Token();
		}
		return $t;
	}
	
	private function prevToken($token, $offset = 1) {
		$index = $this->tokens->indexOf($token);
		$t = $this->tokens->get($index - $offset);
		if (empty($t)) {
			$t = new Token();
		}
		return $t;
	}
	
	private function isFunctionInvocation($token) {
		$prevToken = $this->prevToken($token);
		return $token->contents == '(' && $prevToken->type == T_STRING;
	}
	
	public function getCode() {
		return $this->writer->getContent();
	}
}