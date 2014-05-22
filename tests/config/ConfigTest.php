<?php

namespace gossi\formatter\tests\config;

use gossi\formatter\config\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase {

	public function testConfig() {
		$expected = [
			'indentation' => [
				'character' => 'tab',
				'size' => 1,
				'struct' => 1,
				'function' => 1,
				'blocks' => 1,
				'switch' => 1,
				'case' => 1,
				'break' => 1,
				'empty_lines' => false
			],
			
			'braces' => [
				'struct' => 'same',
				'function' => 'same',
				'blocks' => 'same'
			],
			
			'whitespace' => [
				'before_curly' => true,
				'before_open' => true,
				'after_open' => false,
				'before_close' => false,
				'before_comma' => false,
				'after_comma' => true,
				'before_semicolon' => false,
				'after_semicolon' => true,
				'before_arrow' => false,
				'after_arrow' => false,
				'before_doublecolon' => false,
				'after_doublecolon' => false,
				'before_binary' => true,
				'after_binary' => true,
				'before_unary' => true,
				'after_unary' => false,
				'before_prefix' => false,
				'after_prefix' => false,
				'before_postfix' => false,
				'after_postfix' => true,
				'before_questionmark' => true,
				'after_questionmark' => true,
				'before_colon' => true,
				'after_colon' => true
			],
			
			'newlines' => [
				'elseif_else' => false,
				'catch' => false,
				'finally' => false,
				'do_while' => false
			],
			
			'blanks' => [
				'before_namespace' => 0,
				'after_namespace' => 1,
				'after_use' => 1,
				'before_struct' => 1,
				'before_traits' => 1,
				'before_constant' => 1,
				'before_properties' => 1,
				'before_function' => 1,
				'beginning_function' => 0,
				'end_function' => 0,
				'end_struct' => 1,
				'end_file' => 1
			]
		];
		
		$config = new Config();
		
		$this->assertEquals($expected, $config->getconfig());
	}
	
	public function testIndentation() {
		$config = new Config();
		
		$this->assertEquals('tab', $config->getIndentation('character'));
	}
	
	public function testBraces() {
		$config = new Config();
		
		$this->assertEquals('same', $config->getBraces('struct'));
	}
	
	public function testWhitespace() {
		$config = new Config();
		
		$this->assertTrue($config->getWhitespace('before_curly'));
		$this->assertFalse($config->getWhitespace('after_open'));
	}
}