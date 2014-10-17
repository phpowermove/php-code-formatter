<?php
namespace gossi\formatter\entities;

class Unit {

	const UNIT_NAMESPACE = 'namespace';
	const UNIT_USE = 'use';
	const UNIT_TRAITS = 'traits';
	const UNIT_FIELDS = 'fields';
	const UNIT_CONSTANTS = 'constants';
	const UNIT_METHODS = 'methods';
	
	/** @var Token */
	public $start = null;
	
	/** @var Token */
	public $end = null;
	public $type = '';
}