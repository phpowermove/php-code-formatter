<?php
namespace gossi\formatter\traverse;

use gossi\formatter\token\Token;
use gossi\collection\Stack;
use gossi\formatter\token\Tokenizer;
use gossi\formatter\token\TokenVisitorInterface;
use gossi\formatter\entities\Block;
use Symfony\Component\EventDispatcher\EventDispatcher;
use gossi\formatter\events\TokenEvent;
use gossi\formatter\entities\Group;
use gossi\formatter\events\BlockEvent;
use gossi\formatter\events\GroupEvent;

class ContextManager implements TokenVisitorInterface {
	
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
	private $isStructBody = false;
	private $isRoutineBody = false;

	// stacks
	private $blockStack;
	private $groupStack;
	private $contextStack;
	private $line;

	// helpers
	private $blockDetected;
	private $tracker;
	private $dispatcher;
	
	public function __construct() {
		$this->blockStack = new Stack();
		$this->groupStack = new Stack();
		$this->contextStack = new Stack();
		$this->semanticBlocks = array_merge(Tokenizer::$STRUCTURAL, 
				[T_PUBLIC, T_PRIVATE, T_PROTECTED, T_ABSTRACT, T_STATIC, T_VAR]);
		
		$this->dispatcher = new EventDispatcher();
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

	public function visit(Token $token) {
		// load current contexts
		$this->block = $this->peekBlock();
		$this->group = $this->peekGroup();
		
		// detect new contexts
		$this->detectBlockContext($token);
		$this->detectGroupContext($token);
		$this->detectLineContext($token);
	}

	private function detectBlockContext(Token $token) {
		if (in_array($token->type, Tokenizer::$BLOCKS)
				|| in_array($token->type, Tokenizer::$STRUCTURAL)) {
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
		if ($token->contents == '{') {
			$type = Block::getType($this->blockDetected);
			if ($type == Block::TYPE_FUNCTION && $this->getCurrentContext() == self::CONTEXT_STRUCT) {
				$type = Block::TYPE_METHOD;
			}
			$block = new Block($type);
			$block->start = $token;
			$this->blockStack->push($block);
			$this->block = $block;
			$this->blockDetected = null;

			$event = new BlockEvent($token, $this->block);
			$this->dispatcher->dispatch(self::EVENT_BLOCK_ENTER, $event);

			// enter struct context
			if (in_array($block->type, Block::$STRUCTS)) {
				$this->isStructBody = true;
				$this->dispatcher->dispatch(self::EVENT_STRUCT_ENTER, $event);
				$this->contextStack->push(self::CONTEXT_STRUCT);
			}

			// enter routine context
			else if (in_array($block->type, Block::$ROUTINE)) {
				$this->isRoutineBody = true;
				$this->dispatcher->dispatch(self::EVENT_ROUTINE_ENTER, $event);
				$this->contextStack->push(self::CONTEXT_ROUTINE);
			}

			// enter block context
			else {
				$this->contextStack->push(self::CONTEXT_BLOCK);
			}
		}
	}

	private function leaveBlockContext(Token $token) {
		if ($token->contents == '}') {
			$this->block = $this->blockStack->pop();

			$event = new BlockEvent($token, $this->block);
			$this->dispatcher->dispatch(self::EVENT_BLOCK_LEAVE, $event);
			$this->contextStack->pop();
			
			// leave struct context
			if ($this->isStructBody && in_array($this->block->type, Block::$STRUCTS)) {
				$this->isStructBody = false;
				$this->dispatcher->dispatch(self::EVENT_STRUCT_LEAVE, $event);
			}

			// leave routine context
			else if ($this->isRoutineBody && in_array($this->block->type, Block::$ROUTINE)) {
				$this->isRoutineBody = false;
				$this->dispatcher->dispatch(self::EVENT_ROUTINE_LEAVE, $event);
			}
		}
	}
	
	public function isBlockContextDetected() {
		return $this->blockDetected !== null;
	}
	
	public function isStructBody() {
		return $this->isStructBody;
	}
	
	public function isRoutineBody() {
		return $this->isRoutineBody;
	}

	private function detectGroupContext(Token $token) {
		$prevToken = $this->tracker->getPrevToken();
		if ($token->contents == '(') {
			$this->group = new Group();
			$this->group->start = $token;
			if (in_array($prevToken->type, Tokenizer::$BLOCKS)
					|| in_array($prevToken->type, Tokenizer::$OPERATORS)) {
				$this->group->type = Group::BLOCK;
				$this->group->token = $prevToken;
			} else if ($this->isFunctionInvocation($token)) {
				$this->group->type = Group::CALL;
				$this->group->token = $prevToken;
			} else {
				$this->group->type = Group::GROUP;
			}

			$this->groupStack->push($this->group);
			
			$event = new GroupEvent($token, $this->group);
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
		if (in_array($token->contents, Tokenizer::$LINE_CONTEXT)) {
			$this->line = $token->contents;
		}
	}

	public function resetLineContext() {
		$this->line = null;
	}
	
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
		return new Token();
	}
	
	private function peekGroup() {
		if ($this->groupStack->size() > 0) {
			return $this->groupStack->peek();
		}
		return new Group();
	}
}