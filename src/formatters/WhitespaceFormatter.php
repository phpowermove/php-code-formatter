<?php
namespace gossi\formatter\formatters;

use gossi\formatter\entities\Group;
use phootwork\tokenizer\Token;

class WhitespaceFormatter extends SpecializedFormatter {

	private static $BLOCK_CONTEXT_MAPPING = [
		T_IF => 'ifelse',
		T_ELSEIF => 'ifelse',
		T_WHILE => 'while',
		T_FOREACH => 'foreach',
		T_FOR => 'for',
		T_CATCH => 'catch'
	];

	private static $SYNTAX = [
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

	protected function doVisitToken(Token $token) {
		$this->applyKeywords($token);
		$this->applyAssignments($token);
		$this->applyOperators($token);
		$this->applyPrefixPostfix($token);
		$this->applyUnary($token);
		$this->applySyntax($token);
	}

	private function applyKeywords(Token $token) {
		if ($this->matcher->isKeyword($token)) {
			$this->defaultFormatter->addPostWrite(' ');
		}
	}

	private function applyAssignments(Token $token) {
		if ($this->matcher->isAssignment($token)) {
			$this->whitespaceBeforeAfter('assignment', 'assignments');
		}
	}

	private function applyOperators(Token $token) {
		if ($this->matcher->isOperator($token)) {
			$this->whitespaceBeforeAfter('binary', 'operators');
		}
	}

	private function applyPrefixPostfix(Token $token) {
		if ($token->type == T_INC || $token->type == T_DEC) {
			// pre
			if ($this->nextToken->type == T_VAR) {
				$this->whitespaceBeforeAfter('prefix', 'operators');
			}

			// post
			else if ($this->prevToken->type == T_VAR) {
				$this->whitespaceBeforeAfter('postfix', 'operators');
			}
		}
	}

	/**
	 * @TODO
	 * @param Token $token
	 */
	private function applyUnary(Token $token) {

	}

	private function applySyntax(Token $token) {
		if (array_key_exists($token->contents, self::$SYNTAX)) {
			$key = self::$SYNTAX[$token->contents];
			$group = $this->context->getGroupContext();

			// return when semicolon is not inside a block context
			if ($token->contents == ';' && $group->type != Group::BLOCK) {
				return;
			}

			// anyway find context and apply it
			$context = $this->findContext($token);
			$this->whitespaceBeforeAfter($key, $context);
		}
	}

	private function findContext(Token $token) {
		$group = $this->context->getGroupContext();
		$context = 'default';

		// first check the context of the current line
		$line = $this->context->getLineContext();
		if (!empty($line)) {
			$context = $line;
		}

		// is it a parens group?
		else if ($group->type == Group::GROUP) {
			$context = 'grouping';
		}

		// a function call?
		else if ($group->type == Group::CALL) {
			$context = 'function_invocation';
		}

		// field access?
		else if ($token->contents === '->' || $token->contents === '::') {
			$context = 'field_access';
		}

		// or a given block statement?
		else if ($group->type == Group::BLOCK
				&& isset(self::$BLOCK_CONTEXT_MAPPING[$group->token->type])) {
			$context = self::$BLOCK_CONTEXT_MAPPING[$group->token->type];
		}

		return $context;
	}

	private function whitespaceBeforeAfter($key, $context = 'default') {
		if ($this->config->getWhitespace('before_' . $key, $context)) {
			$this->defaultFormatter->addPreWrite(' ');
		}

		if ($this->config->getWhitespace('after_' . $key, $context)) {
			$this->defaultFormatter->addPostWrite(' ');
		}
	}

}
