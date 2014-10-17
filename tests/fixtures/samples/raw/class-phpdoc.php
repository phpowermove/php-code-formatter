<?php

namespace a\b\c;
use a\b\Gamma; 
/**
 * PHPDoc for class
 *
 * @author gossi
 */
class D {
	use Alpha;
	const XYZ = 'xyz';
	public $content;
	/**
	 * @var unknown
	 */
	public $type;
	/**
	 * PHPDoc for Constructor
	 * 
	 * With linebreaks - wooo!
	 */
	public function __construct() {
		// do something
		if ($this->content == self::XYZ) {
			doSomething();
		}
	}
	/**
	 * Description for mthd
	 * 
	 * @see #__construct
	 * 
	 * @param string $param   Description for $param
	 * @return void
	 */
	public function mthd($param = false) {
		
	}
}