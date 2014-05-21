<?php

namespace gossi\formatter\token;

class TokenCollection implements \Iterator {
	
	private $collection = [];
	
	public function __construct() {
		
	}
	
	public function add(Token $token) {
		$this->collection[] = $token;
	}
	
	public function remove(Token $token) {
		$index = array_search($token, $this->collection);
		if ($index !== null) {
			unset($this->collection[$index]);
		}
	}
	
	public function indexOf(Token $token) {
		return array_search($token, $this->collection);
	}
	
	public function next($offset) {
		$size = count($this->collection);
		do {
			$offset++;
			$token = $this->collection[$offset];
		} while ($token->type == T_WHITESPACE && $offset < $size);

		return [$offset, $token];
	}
	
	public function previous($offset) {
		do {
			$offset--;
			$token = $this->collection[$offset];
		} while ($token->type == T_WHITESPACE && $offset >= 0);
	
		return [$offset, $token];
	}
	
	/**
	 * @internal
	 */
	function rewind() {
		reset($this->collection);
	}
	
	/**
	 * @internal
	 */
	function current() {
		return current($this->collection);
	}
	
	/**
	 * @internal
	 */
	function key() {
		return key($this->collection);
	}
	
	/**
	 * @internal
	 */
	function next() {
		next($this->collection);
	}
	
	/**
	 * @internal
	 */
	function valid() {
		return key($this->collection) !== null;
	}
}