<?php
namespace gossi\formatter\formatters;

use gossi\formatter\token\Token;
use gossi\formatter\token\Tokenizer;
use gossi\formatter\traverse\ContextManager;

class WhitespaceFormatter extends AbstractSpecializedFormatter {
	
	private static $BLOCK_CONTEXT_MAPPING = [
		T_IF => 'ifelse',
		T_ELSEIF => 'ifelse',
		T_WHILE => 'while',
		T_FOREACH => 'foreach',
		T_FOR => 'for',
		T_CATCH => 'catch'
	];
	
	protected function doVisit(Token $token) {
		$parens = $this->context->getParensContext();
		$parensToken = $this->context->getParensTokenContext();
		
		// keywords
		if (in_array($token->type, Tokenizer::$KEYWORDS)) {
			$this->defaultFormatter->hideToken();
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
			if ($this->nextToken->type == T_VAR) {
				$this->whitespaceBeforeAfter($token, 'prefix', 'operators');
			}
				
			// post
			else if ($this->prevToken->type == T_VAR) {
				$this->whitespaceBeforeAfter($token, 'postfix', 'operators');
			}
		}
		
		// unary operator @TODO
		

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

				// is it a parens group?
				else if ($parens == ContextManager::LEXICAL_GROUP) {
					$context = 'grouping';
				}

				// a function call?
				else if ($parens == ContextManager::LEXICAL_CALL) {
					$context = 'function_invocation';
				}

				// field access?
				else if ($contents === '->' || $contents === '::') {
					$context = 'field_access';
				}

				// or a given block statement?
				else if ($parens == ContextManager::LEXICAL_BLOCK
						&& isset(self::$BLOCK_CONTEXT_MAPPING[$parensToken->type])) {
					$context = self::$BLOCK_CONTEXT_MAPPING[$parensToken->type];
				}

				$this->whitespaceBeforeAfter($token, $key, $context);
			}
		}
		
	}

	private function whitespaceBeforeAfter(Token $token, $key, $context = 'default') {
		if ($this->config->getWhitespace('before_' . $key, $context)) {
			$this->writer->write(' ');
		}
	
		$this->defaultFormatter->hideToken();
		$this->writer->write($token->contents);
		
		if ($this->config->getWhitespace('after_' . $key, $context)) {
			$this->writer->write(' ');
		}
	}
	
}
