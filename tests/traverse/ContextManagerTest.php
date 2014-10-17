<?php
namespace gossi\formatter\tests\traverse;

use gossi\formatter\Formatter;
use gossi\formatter\tests\utils\SamplesTrait;
use gossi\formatter\token\Token;
use gossi\formatter\token\Tokenizer;
use gossi\formatter\traverse\ContextManager;
use gossi\formatter\traverse\TokenTracker;
use gossi\collection\ArrayList;
use gossi\formatter\events\TokenEvent;
use gossi\formatter\events\BlockEvent;
use gossi\formatter\events\GroupEvent;

class ContextManagerTest extends \PHPUnit_Framework_TestCase {

	use SamplesTrait;
	
	protected function setUp() {
		$code = $this->getRawContent('sample1');
		
		$this->tokenizer = new Tokenizer();
		$this->tokens = $this->tokenizer->tokenize($code);
		$this->context = new ContextManager();
		$this->tracker = new TokenTracker($this->tokens, $this->context);
	}
	
	public function testBlockStack() {
		$log = new ArrayList();
		$listener = function(BlockEvent $event) use ($log) {
			$log->add($event->getName() . ' ' . $event->getBlock()->type);
		};
		
		$this->context->addListener(ContextManager::EVENT_BLOCK_ENTER, $listener);
		$this->context->addListener(ContextManager::EVENT_BLOCK_LEAVE, $listener);
		
		foreach ($this->tokens as $token) {
			$this->tracker->visit($token);
		}

		$this->assertEquals([
			'context.block_enter if', 
			'context.block_leave if', 
			'context.block_enter else', 
			'context.block_leave else', 
			'context.block_enter for', 
			'context.block_enter while', 
			'context.block_enter if', 
			'context.block_leave if', 
			'context.block_leave while', 
			'context.block_leave for'], 
			$log->toArray());
	}
	
	public function testParensStack() {
		$log = new ArrayList();
		$listener = function(GroupEvent $event) use ($log) {
			$log->add($event->getName() . ' ' . $event->getGroup()->type);
		};
		
		$this->context->addListener(ContextManager::EVENT_GROUP_ENTER, $listener);
		$this->context->addListener(ContextManager::EVENT_GROUP_LEAVE, $listener);
		
		foreach ($this->tokens as $token) {
			$this->tracker->visit($token);
		}

		$this->assertEquals([
			'context.group_enter block', 
			'context.group_leave block', 
			'context.group_enter block', 
			'context.group_leave block', 
			'context.group_enter block', 
			'context.group_enter group', 
			'context.group_leave group', 
			'context.group_leave block', 
			'context.group_enter call', 
			'context.group_leave call', 
			'context.group_enter block', 
			'context.group_leave block'],
			$log->toArray());
	}
}