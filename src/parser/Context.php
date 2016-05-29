<?php
namespace gossi\formatter\parser;

use gossi\formatter\entities\Block;
use gossi\formatter\entities\Group;
use gossi\formatter\events\BlockEvent;
use gossi\formatter\events\GroupEvent;
use phootwork\collection\Stack;
use phootwork\tokenizer\Token;
use phootwork\tokenizer\TokenVisitorInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Context implements TokenVisitorInterface {

	// context constants
	const CONTEXT_FILE = 'file';
	const CONTEXT_STRUCT = 'struct';
	const CONTEXT_ROUTINE = 'routine';
	const CONTEXT_BLOCK = 'block';
	const CONTEXT_GROUP = 'group';

	// event constants
	const EVENT_BLOCK_ENTER = 'context.block_enter';
	const EVENT_BLOCK_LEAVE = 'context.block_leave';
	const EVENT_STRUCT_ENTER = 'context.struct_enter';
	const EVENT_STRUCT_LEAVE = 'context.struct_leave';
	const EVENT_ROUTINE_ENTER = 'context.routine_enter';
	const EVENT_ROUTINE_LEAVE = 'context.routine_leave';
	const EVENT_GROUP_ENTER = 'context.group_enter';
	const EVENT_GROUP_LEAVE = 'context.group_leave';

	// current contexts
	private $block;
	private $group;
	private $inStructBody = false;
	private $inRoutineBody = false;

	// stacks
	private $blockStack;
	private $groupStack;
	private $contextStack;
	private $line;

	// helpers
	private $blockDetected;
	private $tracker;
	private $dispatcher;
	private $parser;
	private $matcher;
	private $events = [
		self::EVENT_BLOCK_ENTER, self::EVENT_BLOCK_LEAVE,
		self::EVENT_GROUP_ENTER, self::EVENT_GROUP_LEAVE,
		self::EVENT_ROUTINE_ENTER, self::EVENT_ROUTINE_LEAVE,
		self::EVENT_STRUCT_ENTER, self::EVENT_STRUCT_LEAVE
	];

	private static $PROPERTIES = [T_PRIVATE, T_PUBLIC, T_PROTECTED, T_STATIC, T_VAR];

	public function __construct(Parser $parser) {
		$this->parser = $parser;
		$this->matcher = $parser->getMatcher();
		$this->blockStack = new Stack();
		$this->groupStack = new Stack();
		$this->contextStack = new Stack();
		$this->dispatcher = new EventDispatcher();
	}

	public function reset() {
		// remove listeners
		foreach ($this->events as $event) {
			$listeners = $this->dispatcher->getListeners($event);
			foreach ($listeners as $listener) {
				$this->dispatcher->removeListener($event, $listener);
			}
		}

		// reset data objects
		$this->blockStack->clear();
		$this->groupStack->clear();
		$this->contextStack->clear();
	}

	public function setTracker(TokenTracker $tracker) {
		$this->tracker = $tracker;
	}

	public function addListener($name, $listener) {
		$this->dispatcher->addListener($name, $listener);
	}

	public function removeListener($name, $listener) {
		$this->dispatcher->removeListener($name, $listener);
	}

	public function visitToken(Token $token) {
		// load current contexts
		$this->block = $this->peekBlock();
		$this->group = $this->peekGroup();

		// detect new contexts
		$this->detectBlockContext($token);
		$this->detectGroupContext($token);
		$this->detectLineContext($token);
	}

	private function detectBlockContext(Token $token) {
		if ($this->matcher->isBlock($token)) {
			$this->blockDetected = $token;
		}

		$this->enterBlockContext($token);
		$this->leaveBlockContext($token);

		// neglect block detection
		if ($this->blockDetected !== null && $token->contents == ';'
				&& ($this->group !== null ? !($this->group->type == Group::BLOCK
					|| $this->group->type == Group::GROUP) : true)) {
			$this->blockDetected = null;
		}
	}

	private function enterBlockContext(Token $token) {
		if ($token->contents == '{' && $this->blockDetected !== null) {
			$type = Block::getType($this->blockDetected);
			if ($type == Block::TYPE_FUNCTION && $this->getCurrentContext() == self::CONTEXT_STRUCT) {
				$type = Block::TYPE_METHOD;
			}
			$block = new Block($type);
			$block->start = $this->findBlockStart($this->blockDetected);
			$block->open = $token;
			$this->blockStack->push($block);
			$this->block = $block;
			$this->blockDetected = null;

			$event = new BlockEvent($token, $this->block);
			$this->dispatcher->dispatch(self::EVENT_BLOCK_ENTER, $event);

			// enter struct context
			if ($block->isStruct()) {
				$this->inStructBody = true;
				$this->dispatcher->dispatch(self::EVENT_STRUCT_ENTER, $event);
				$this->contextStack->push(self::CONTEXT_STRUCT);
			}

			// enter routine context
			else if ($block->isRoutine()) {
				$this->inRoutineBody = true;
				$this->dispatcher->dispatch(self::EVENT_ROUTINE_ENTER, $event);
				$this->contextStack->push(self::CONTEXT_ROUTINE);
			}

			// enter block context
			else {
				$this->contextStack->push(self::CONTEXT_BLOCK);
			}
		}
	}

	private function findBlockStart(Token $token) {
		$startToken = $token;
		$prevToken = $this->tracker->prevToken($token);

		while ($this->matcher->isModifier($prevToken)) {
			$startToken = $prevToken;
			$prevToken = $this->tracker->prevToken($prevToken);
		}

		return $startToken;
	}

	private function leaveBlockContext(Token $token) {
		if ($token->contents == '}') {
			$this->block = $this->blockStack->pop();

			// find block end
			if ($this->block->type == Block::TYPE_DO) {
				$nextToken = $token;
				do {
					$nextToken = $this->tracker->nextToken($nextToken);
				} while ($nextToken->contents != ';');
				$this->block->end = $nextToken;
			} else {
				$this->block->end = $token;
			}

			$event = new BlockEvent($token, $this->block);
			$this->dispatcher->dispatch(self::EVENT_BLOCK_LEAVE, $event);
			$this->contextStack->pop();

			// leave struct context
			if ($this->inStructBody && $this->block->isStruct()) {
				$this->inStructBody = false;
				$this->dispatcher->dispatch(self::EVENT_STRUCT_LEAVE, $event);
			}

			// leave routine context
			else if ($this->inRoutineBody && $this->block->isRoutine()) {
				$this->inRoutineBody = false;
				$this->dispatcher->dispatch(self::EVENT_ROUTINE_LEAVE, $event);
			}
		}
	}

	public function isBlockContextDetected() {
		return $this->blockDetected !== null;
	}

	/**
	 * Tells you, whenever being in a struct, this is also true when inside 
	 * a method or inside a function, which is inside a method
	 * 
	 * @return bool
	 */
	public function inStructBody() {
		return $this->inStructBody;
	}

	/**
	 * Tells you, whenever being in a function or method
	 * 
	 * @return bool
	 */
	public function inRoutineBody() {
		return $this->inRoutineBody;
	}

	private function detectGroupContext(Token $token) {
		$prevToken = $this->tracker->getPrevToken();
		if ($token->contents == '(') {
			$group = new Group();
			$group->start = $token;
// 			if (in_array($prevToken->type, Tokenizer::$BLOCKS)
// 					|| in_array($prevToken->type, Tokenizer::$OPERATORS)) {
			if (($this->matcher->isBlock($prevToken) || $this->matcher->isOperator($prevToken))
					&& !$this->group->isBlock()) {
				$group->type = Group::BLOCK;
				$group->token = $prevToken;
			} else if ($this->isFunctionInvocation($token)) {
				$group->type = Group::CALL;
				$group->token = $prevToken;
			} else {
				$group->type = Group::GROUP;
			}

			$this->groupStack->push($group);
			$this->group = $group;

			$event = new GroupEvent($token, $group);
			$this->dispatcher->dispatch(self::EVENT_GROUP_ENTER, $event);
		} else if ($token->contents == ')') {
			$this->group = $this->groupStack->pop();
			$this->group->end = $token;

			$event = new GroupEvent($token, $this->group);
			$this->dispatcher->dispatch(self::EVENT_GROUP_LEAVE, $event);
		}
	}

	private function isFunctionInvocation($token) {
		$prevToken = $this->tracker->getPrevToken();
		return $token->contents == '(' && $prevToken->type == T_STRING;
	}

	private function detectLineContext(Token $token) {
		if ($this->matcher->isLineContext($token)) {
			$this->line = $token->contents;
		}
	}

	public function resetLineContext() {
		$this->line = null;
	}

	/**
	 * Returns the line context or null if not present
	 * 
	 * @return string|null
	 */
	public function getLineContext() {
		return $this->line;
	}

	/**
	 * Returns the current context. Context is one of the Context::CONTEXT_* constants.
	 * 
	 * @return string
	 */
	public function getCurrentContext() {
		if ($this->contextStack->size() > 0) {
			return $this->contextStack->peek();
		}

		return self::CONTEXT_FILE;
	}

	/**
	 * Returns the current block context
	 * 
	 * @return Block
	 */
	public function getBlockContext() {
		return $this->block;
	}

	/**
	 * Returns the current group context
	 * 
	 * @return Group|null
	 */
	public function getGroupContext() {
		return $this->group;
	}

	private function peekBlock() {
		if ($this->blockStack->size() > 0) {
			return $this->blockStack->peek();
		}
		return new Block(null);
	}

	private function peekGroup() {
		if ($this->groupStack->size() > 0) {
			return $this->groupStack->peek();
		}
		return new Group();
	}
}
