<?php

namespace gossi\formatter\token;

class TokenCollection implements \Iterator, \ArrayAccess, \Countable {
	
	private $collection = [];
	
	public function __construct($collection = []) {
		$this->collection = $collection;
	}
	
	public function add(Token $token) {
		$this->collection[] = $token;
	}
	
	public function get($index) {
		if (isset($this->collection[$index])) {
			return $this->collection[$index];
		}
	}

	public function remove(Token $token) {
		$index = array_search($token, $this->collection, true);
		if ($index !== null) {
			unset($this->collection[$index]);
		}
	}
	
	public function indexOf(Token $token) {
		return array_search($token, $this->collection, true);
	}
	
	public function nextToken($offset) {
		$size = count($this->collection);
		do {
			$offset++;
			$token = $this->collection[$offset];
		} while ($token->type == T_WHITESPACE && $offset < $size);

		return [$offset, $token];
	}
	
	public function prevToken($offset) {
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
	
	/**
	 * @internal
	 */
	public function offsetSet($offset, $value) {
		if (is_null($offset)) {
			$this->collection[] = $value;
		} else {
			$this->collection[$offset] = $value;
		}
	}

	/**
	 * @internal
	 */
	public function offsetExists($offset) {
		return isset($this->collection[$offset]);
	}
	
	/**
	 * @internal
	 */
	public function offsetUnset($offset) {
		unset($this->collection[$offset]);
	}
	
	/**
	 * @internal
	 */
	public function offsetGet($offset) {
		return isset($this->collection[$offset]) ? $this->collection[$offset] : null;
	}
	
	/**
	 * @internal
	 */
	public function count() {
		return count($this->collection);
	}

}