<?php
namespace gossi\formatter\entities;

use phootwork\tokenizer\Token;

class Block {

	// struct types
	const TYPE_CLASS = 'class';
	const TYPE_TRAIT = 'trait';
	const TYPE_INTERFACE = 'interface';

	// routine types
	const TYPE_FUNCTION = 'function';
	const TYPE_METHOD = 'method';

	// block types
	const TYPE_IF = 'if';
	const TYPE_ELSEIF = 'elseif';
	const TYPE_ELSE = 'else';
	const TYPE_DO = 'do';
	const TYPE_WHILE = 'while';
	const TYPE_FOR = 'for';
	const TYPE_FOREACH = 'foreach';
	const TYPE_SWITCH = 'switch';
	const TYPE_USE = 'use';
	const TYPE_NAMESPACE = 'namespace';
	const TYPE_TRY = 'try';
	const TYPE_CATCH = 'catch';
	const TYPE_FINALLY = 'finally';

	private static $typeMap = [
		T_NAMESPACE => self::TYPE_NAMESPACE,
		T_CLASS => self::TYPE_CLASS,
		T_TRAIT => self::TYPE_TRAIT,
		T_INTERFACE => self::TYPE_INTERFACE,
		T_FUNCTION => self::TYPE_FUNCTION,
		T_IF => self::TYPE_IF,
		T_ELSEIF => self::TYPE_ELSEIF,
		T_ELSE => self::TYPE_ELSE,
		T_DO => self::TYPE_DO,
		T_WHILE => self::TYPE_WHILE,
		T_FOR => self::TYPE_FOR,
		T_FOREACH => self::TYPE_FOREACH,
		T_SWITCH => self::TYPE_SWITCH,
		T_USE => self::TYPE_USE,
		T_TRY => self::TYPE_TRY,
		T_CATCH => self::TYPE_CATCH,
	];

	private static $STRUCTS = [self::TYPE_CLASS, self::TYPE_TRAIT, self::TYPE_INTERFACE];
	private static $ROUTINE = [self::TYPE_FUNCTION, self::TYPE_METHOD];
	private static $BLOCKS = [self::TYPE_IF, self::TYPE_ELSEIF, self::TYPE_ELSE, self::TYPE_DO,
		self::TYPE_WHILE, self::TYPE_FOR, self::TYPE_FOREACH, self::TYPE_SWITCH, self::TYPE_USE,
			self::TYPE_NAMESPACE];

	/** 
	 * The opening curly brace
	 * 
	 * @var Token 
	 */
	public $open = null;

	/** 
	 * The closing curly brace
	 * 
	 * @var Token 
	 */
	public $close = null;

	/**
	 * Start is the initial token of that block
	 *  
	 * @var Token
	 */
	public $start = null;

	/**
	 * Start is the last token of that block
	 *
	 * @var Token
	 */
	public $end = null;

	public $type = '';

	public function __construct($type) {
		$this->type = $type;
	}

	public function isStruct() {
		return in_array($this->type, self::$STRUCTS);
	}

	public function isRoutine() {
		return in_array($this->type, self::$ROUTINE);
	}

	public function isBlock() {
		return in_array($this->type, self::$BLOCKS);
	}

	public static function getType(Token $token) {
		if (isset(self::$typeMap[$token->type])) {
			return self::$typeMap[$token->type];
		} else if ($token->contents == 'finally') {
			return self::TYPE_FINALLY;
		}
	}
}
