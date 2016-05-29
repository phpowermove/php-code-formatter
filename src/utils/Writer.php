<?php
namespace gossi\formatter\utils;

/**
 * A writer implementation.
 *
 * This may be used to simplify writing well-formatted code.
 *
 */
class Writer {

	private $content = '';
	private $indentationLevel = 0;
	private $indentation;

	private $options = [
		'indentation_character' => "\t",
		'indentation_size' => 1
	];

	public function __construct($options = []) {
		$this->options = array_merge($this->options, $options);

		$this->indentation = str_repeat($this->options['indentation_character'],
				$this->options['indentation_size']);
	}

	public function indent() {
		$this->indentationLevel += 1;

		return $this;
	}

	public function outdent() {
		$this->indentationLevel -= 1;

		if ($this->indentationLevel < 0) {
			throw new \RuntimeException('The identation level cannot be less than zero.');
		}

		return $this;
	}

	/**
	 *
	 * @param string $content        	
	 */
	public function writeln($content = '') {
		$this->write($content . "\n");

		return $this;
	}

	/**
	 *
	 * @param string $content        	
	 */
	public function write($content) {
		$lines = explode("\n", $content);
		for ($i = 0, $c = count($lines); $i < $c; $i ++) {
			if ($this->indentationLevel > 0
					&& !empty($lines[$i])
					&& (empty($this->content) || "\n" === substr($this->content, -1))) {
				$this->content .= str_repeat($this->indentation, $this->indentationLevel);
			}

			$this->content .= $lines[$i];

			if ($i + 1 < $c) {
				$this->content .= "\n";
			}
		}

		return $this;
	}

	public function rtrim() {
		$addNl = "\n" === substr($this->content, -1);
		$this->content = rtrim($this->content);

		if ($addNl) {
			$this->content .= "\n";
		}

		return $this;
	}

	public function endsWith($search) {
		return substr($this->content, -strlen($search)) === $search;
	}

	public function reset() {
		$this->content = '';
		$this->indentationLevel = 0;

		return $this;
	}

	public function getContent() {
		return $this->content;
	}
}
