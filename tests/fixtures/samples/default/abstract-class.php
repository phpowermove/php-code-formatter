<?php
namespace a\b\c;

abstract class AbstractClass {
	
	protected $prop;
	private static $staticProp;
	
	public function __construct() {
		// do something
	}
	
	abstract public function mthd();
	
	public static function getProp() {
		return self::$staticProp;
	}
	
	protected function secondMethod() {
	}
	
	const FOO = 'BAR';
}
