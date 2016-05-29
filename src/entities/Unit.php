<?php
namespace gossi\formatter\entities;

use phootwork\tokenizer\Token;

class Unit {

	const UNIT_NAMESPACE = 'namespace';
	const UNIT_USE = 'use';
	const UNIT_TRAITS = 'traits';
	const UNIT_FIELDS = 'fields';
	const UNIT_CONSTANTS = 'constants';
	const UNIT_METHODS = 'methods';
	const UNIT_IMPORTS = 'imports';

	private static $typeMap = [
		T_NAMESPACE => self::UNIT_NAMESPACE,
		T_USE => self::UNIT_USE,
		T_CONST => self::UNIT_CONSTANTS,
		T_NAMESPACE => self::UNIT_NAMESPACE,
		T_USE => self::UNIT_USE,
		T_REQUIRE => self::UNIT_IMPORTS,
		T_REQUIRE_ONCE => self::UNIT_IMPORTS,
		T_INCLUDE => self::UNIT_IMPORTS,
		T_INCLUDE_ONCE => self::UNIT_IMPORTS
	];

	/** @var Token */
	public $start = null;

	/** @var Token */
	public $end = null;
	public $type = '';

	public static function getType(Token $token) {
		if (isset(self::$typeMap[$token->type])) {
			return self::$typeMap[$token->type];
		}
	}
}
