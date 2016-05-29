<?php
namespace gossi\formatter\parser;

use phootwork\collection\Set;
use phootwork\tokenizer\Token;

class TokenMatcher {

	// Analyzer
	private static $PROPERTIES = [T_PRIVATE, T_PUBLIC, T_PROTECTED, T_STATIC, T_VAR, T_ABSTRACT];
	private static $IDENTIFIER = [T_CONST, T_NAMESPACE, T_USE];

	// Tokenizer
	public static $IMPORT_STATEMENTS = [T_REQUIRE, T_REQUIRE_ONCE, T_INCLUDE, T_INCLUDE_ONCE];

	/**
	 * Keyswords that are followed by a single space
	*/
	public static $KEYWORDS = [T_ABSTRACT, T_CASE, T_CLASS, T_FUNCTION, T_CLONE, T_CONST, T_EXTENDS, T_FINAL, T_GLOBAL, T_IMPLEMENTS, T_INTERFACE, T_NAMESPACE, T_NEW, T_PRIVATE, T_PUBLIC, T_PROTECTED, T_THROW, T_TRAIT, T_USE];
	public static $BLOCKS = [T_IF, T_ELSEIF, T_ELSE, T_FOR, T_FOREACH, T_WHILE, T_DO, T_SWITCH, T_TRY, T_CATCH];
	public static $CASTS = [T_ARRAY_CAST, T_BOOL_CAST, T_DOUBLE_CAST, T_INT_CAST, T_OBJECT_CAST, T_STRING_CAST, T_UNSET_CAST];
	// 	public static $ASSIGNMENTS = [T_AND_EQUAL, T_CONCAT_EQUAL, T_DIV_EQUAL, T_MINUS_EQUAL, T_MOD_EQUAL, T_MUL_EQUAL, T_OR_EQUAL, T_PLUS_EQUAL, T_SL_EQUAL, T_SR_EQUAL, T_XOR_EQUAL];
	// 	public static $OPERATORS = [T_BOOLEAN_AND, T_BOOLEAN_OR, T_INSTANCEOF, T_IS_EQUAL, T_IS_GREATER_OR_EQUAL, T_IS_IDENTICAL, T_IS_NOT_EQUAL, T_IS_NOT_IDENTICAL, T_IS_SMALLER_OR_EQUAL, T_LOGICAL_AND, T_LOGICAL_OR, T_LOGICAL_XOR, T_SL, T_SR];
	public static $ASSIGNMENTS = ['=', '&=', '.=', '/=', '-=', '%=', '*=', '|=', '+=', '**=', '<<=', '>>=', '^='];
	public static $OPERATORS = ['&', '&&', 'and', '|', '||', 'or', '^', 'xor', 'instanceof', '==', '>=', '>', '===', '!=', '<>', '!==', '<=', '<', '<<', '>>', '+', '-', '*', '/', '**', 'as'];
	public static $STRUCTURAL = [T_CLASS, T_INTERFACE, T_TRAIT, T_NAMESPACE, T_USE, T_FUNCTION];
	public static $STRUCTS = [T_CLASS, T_INTERFACE, T_TRAIT];
	public static $LINE_CONTEXT = ['echo', 'global', 'static', 'yield', 'case'];

	/** @var Set */
	private $keywords;
	/** @var Set */
	private $blocks;
	/** @var Set */
	private $casts;
	/** @var Set */
	private $assignments;
	/** @var Set */
	private $operators;
	/** @var Set */
	private $structural;
	/** @var Set */
	private $structs;
	/** @var Set */
	private $lineContext;
	/** @var Set */
	private $imports;
	/** @var Set */
	private $unitIdentifier;
	/** @var Set */
	private $modifier;

	public function __construct() {
		$this->keywords = new Set([T_ABSTRACT, T_CASE, T_CLASS, T_FUNCTION, T_CLONE, T_CONST,
				T_EXTENDS, T_FINAL, T_GLOBAL, T_IMPLEMENTS, T_INTERFACE, T_NAMESPACE, T_NEW,
				T_PRIVATE, T_PUBLIC, T_PROTECTED, T_THROW, T_TRAIT, T_USE]);

		$this->blocks = new Set([T_IF, T_ELSEIF, T_ELSE, T_FOR, T_FOREACH, T_WHILE,
				T_DO, T_SWITCH, T_TRY, T_CATCH, T_CLASS, T_INTERFACE, T_TRAIT,
				T_NAMESPACE, T_USE, T_FUNCTION]);

		$this->casts = new Set([T_ARRAY_CAST, T_BOOL_CAST, T_DOUBLE_CAST, T_INT_CAST,
				T_OBJECT_CAST, T_STRING_CAST, T_UNSET_CAST]);

		$this->assignments = new Set(['=', '&=', '.=', '/=', '-=', '%=', '*=', '|=', '+=',
				'**=', '<<=', '>>=', '^=']);

		$this->operators = new Set(['&', '&&', 'and', '|', '||', 'or', '^', 'xor',
				'instanceof', '==', '>=', '>', '===', '!=', '<>', '!==', '<=', '<', '<<',
				'>>', '+', '-', '*', '/', '**', 'as']);

		$this->structural = new Set([T_CLASS, T_INTERFACE, T_TRAIT, T_NAMESPACE, T_USE, T_FUNCTION]);

		$this->structs = new Set([T_CLASS, T_INTERFACE, T_TRAIT]);

		$this->lineContext = new Set(['echo', 'global', 'static', 'yield', 'case']);

		$this->imports = new Set([T_REQUIRE, T_REQUIRE_ONCE, T_INCLUDE, T_INCLUDE_ONCE]);

		$this->unitIdentifier = new Set([T_CONST, T_NAMESPACE, T_USE]);
		$this->unitIdentifier->addAll($this->imports);

		$this->modifier = new Set([T_PRIVATE, T_PUBLIC, T_PROTECTED, T_STATIC, T_VAR, T_ABSTRACT]);
	}

	public function isKeyword(Token $token) {
		return $this->keywords->contains($token->type);
	}

	public function isBlock(Token $token) {
		return $this->blocks->contains($token->type);
	}

	public function isCast(Token $token) {
		return $this->blocks->contains($token->type);
	}

	public function isAssignment(Token $token) {
		return $this->assignments->contains($token->contents);
	}

	public function isOperator(Token $token) {
		return $this->operators->contains($token->contents);
	}

	public function isStruct(Token $token) {
		return $this->operators->contains($token->type);
	}

	public function isLineContext(Token $token) {
		return $this->lineContext->contains($token->contents);
	}

	public function isImport(Token $token) {
		return $this->imports->contains($token->type);
	}

	public function isUnitIdentifier(Token $token) {
		return $this->unitIdentifier->contains($token->type);
	}

	public function isModifier(Token $token) {
		return $this->modifier->contains($token->type);
	}

}
