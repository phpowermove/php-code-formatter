<?php
namespace gossi\formatter\entities;

class Group {

	const BLOCK = 'block';
	const CALL = 'call';
	const GROUP = 'group';
	
	/** @var Token */
	public $start = null;
	
	/** @var Token */
	public $end = null;
	
	/** @var Token */
	public $token = null;

	public $type = '';
}