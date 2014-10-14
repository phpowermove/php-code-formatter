<?php
namespace gossi\formatter\token;

class Block {
	
	const BLOCK_NAMESPACE = 'namespace';
	const BLOCK_USE = 'use';
	const BLOCK_TRAITS = 'traits';
	const BLOCK_FIELDS = 'fields';
	const BLOCK_CONSTANTS = 'constants';
	const BLOCK_METHODS = 'methods';
	
	/** @var Token */
	public $start = null;
	
	/** @var Token */
	public $end = null;
	public $type = '';
}