<?php

namespace gossi\formatter\config;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Definition\Processor;

class Config {

	private $profiles = ['java'];
	private $config;

	public function __construct($profile = null) {
		$profiles = [];
		$configDirectories = [__DIR__ . '/../../profiles'];
		
		$locator = new FileLocator($configDirectories);
		$loader = new YamlLoader($locator);
		
		$isBuiltin = in_array($profile, $this->profiles);
		
		if ($isBuiltin) {
			$profiles[] = $loader->load($locator->locate($profile . '.yml', null, true));
		} else {
			$profiles[] = $loader->load($locator->locate('java.yml', null, true));
		}

		if (!empty($profile) && !$isBuiltin && file_exists($profile)) {
			$profiles[] = $loader->load(file_get_contents($profile));
		}

		$processor = new Processor();
		$definition = new ProfileDefinition();
		$this->config = $processor->processConfiguration($definition, $profiles);
	}
	
	public function getConfig() {
		return $this->config;
	}
}