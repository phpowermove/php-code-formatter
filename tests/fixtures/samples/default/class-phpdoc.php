<?php
namespace a\b\c;

use a\b\Gamma;

/**
 * PHPDoc for class
 * 
 * @author gossi
 */
class D {
	
	const XYZ = 'xyz';
	
	public $content;
	
	/**
	 * PHPDoc for Constructor
	 * 
	 * With linebreaks - wooo!
	 */
	public function __construct() {
		// do something
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
