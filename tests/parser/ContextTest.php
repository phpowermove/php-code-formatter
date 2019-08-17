<?php
namespace gossi\formatter\tests\parser;

use gossi\formatter\entities\Block;
use gossi\formatter\events\BlockEvent;
use gossi\formatter\events\GroupEvent;
use gossi\formatter\parser\Context;
use gossi\formatter\parser\Parser;
use gossi\formatter\tests\utils\SamplesTrait;
use phootwork\collection\ArrayList;

class ContextTest extends \PHPUnit_Framework_TestCase {

	use SamplesTrait;

	private function getCode() {
		return $this->getRawContent('sample1');
	}

	public function testBlockStack() {
		$log = new ArrayList();
		$listener = function (BlockEvent $event) use ($log) {
			$log->add($event->getName() . ' ' . $event->getBlock()->type);
		};

		$parser = new Parser();
		$parser->getContext()->addListener(Context::EVENT_BLOCK_ENTER, $listener);
		$parser->getContext()->addListener(Context::EVENT_BLOCK_LEAVE, $listener);
		$parser->parse($this->getCode());

		$this->assertEquals([
			'context.block_enter if',
			'context.block_leave if',
			'context.block_enter else',
			'context.block_leave else',
			'context.block_enter for',
			'context.block_enter do',
			'context.block_enter if',
			'context.block_leave if',
			'context.block_leave do',
			'context.block_leave for'],
			$log->toArray());
	}

	public function testBlockPositionsOnSample1() {
		$blocks = new ArrayList();
		$listener = function (BlockEvent $event) use ($blocks) {
			$blocks->add($event->getBlock());
		};

		$parser = new Parser();
		$parser->getContext()->addListener(Context::EVENT_BLOCK_LEAVE, $listener);
		$parser->parse($this->getCode());
		$tokens = $parser->getTokens();

		// test: if (first one)
		$if = $blocks->get(0);
		$this->assertEquals(Block::TYPE_IF, $if->type);
		$this->assertEquals($tokens->get(1), $if->start, 'if start token');
		$this->assertEquals($tokens->get(12), $if->end, 'if end token');

		// test: for
		$for = $blocks->get(4);
		$this->assertEquals(Block::TYPE_FOR, $for->type);
		$this->assertEquals($tokens->get(20), $for->start, 'for start token');
		$this->assertEquals($tokens->get(68), $for->end, 'for end token');

		// test: do
		$do = $blocks->get(3);
		$this->assertEquals(Block::TYPE_DO, $do->type);
		$this->assertEquals($tokens->get(34), $do->start, 'do start token');
		$this->assertEquals($tokens->get(67), $do->end, 'do end token');
	}

	public function testBlockPositionsOnAbstractClass() {
		$blocks = new ArrayList();
		$listener = function (BlockEvent $event) use ($blocks) {
			$blocks->add($event->getBlock());
		};

		$parser = new Parser();
		$parser->getContext()->addListener(Context::EVENT_BLOCK_LEAVE, $listener);
		$parser->parse($this->getRawContent('abstract-class'));
		$tokens = $parser->getTokens();

		// test: class
		$class = $blocks->get(3);
		$this->assertEquals(Block::TYPE_CLASS, $class->type);
		$this->assertEquals($tokens->get(8), $class->start);
		$this->assertEquals($tokens->get(59), $class->end);

		// test: static method
		$static = $blocks->get(1);
		$this->assertEquals(Block::TYPE_METHOD, $static->type);
		$this->assertEquals($tokens->get(34), $static->start);
		$this->assertEquals($tokens->get(46), $static->end);
	}

	public function testGroupStack() {
		$log = new ArrayList();
		$listener = function (GroupEvent $event) use ($log) {
			$log->add($event->getName() . ' ' . $event->getGroup()->type);
		};

		$parser = new Parser();
		$parser->getContext()->addListener(Context::EVENT_GROUP_ENTER, $listener);
		$parser->getContext()->addListener(Context::EVENT_GROUP_LEAVE, $listener);
		$parser->parse($this->getCode());

		$this->assertEquals([
				// if
				'context.group_enter block',
				'context.group_leave block',
				// for
				'context.group_enter block',
				'context.group_leave block',
				// call
				'context.group_enter call',
				'context.group_leave call',
				// if
				'context.group_enter block',
				'context.group_leave block',
				// while
				'context.group_enter block',
				// while -> group
				'context.group_enter group',
				'context.group_leave group',
				'context.group_leave block',
			], $log->toArray());
	}
}
