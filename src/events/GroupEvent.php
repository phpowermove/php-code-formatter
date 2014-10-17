<?php
namespace gossi\formatter\events;

use gossi\formatter\token\Token;
use gossi\formatter\entities\Group;

class GroupEvent extends TokenEvent {
	
	/** @var Group */
	private $group;
	
	public function __construct(Token $token, Group $group) {
		parent::__construct($token);
		$this->group = $group;
	}
	
	/**
	 * Returns the associated group
	 * 
	 * @return Group
	 */
	public function getGroup() {
		return $this->group;
	}

}