<?php

namespace gossi\formatter\config;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Definition\Processor;

class Config {

	private $config;

	public function __construct($profile = null) {
		$profileDir = __DIR__ . '/../../profiles';
		
		$locator = new FileLocator([$profileDir]);
		$loader = new YamlLoader($locator);
		$builtIns = $this->readProfiles($loader, $profileDir);
		
		$profiles = [];
		$isBuiltin = in_array($profile, $builtIns);
		
		if ($isBuiltin) {
			$profiles[] = $loader->load($locator->locate($profile . '.yml', null, true));
		} else {
			$profiles[] = $loader->load($locator->locate('default.yml', null, true));
		}

		if (!empty($profile) && !$isBuiltin && file_exists($profile)) {
			$profiles[] = $loader->load(file_get_contents($profile));
		}

		$processor = new Processor();
		$definition = new ProfileDefinition();
		$this->config = $processor->processConfiguration($definition, $profiles);
	}
	
	private function readProfiles(YamlLoader $loader, $profileDir) {
		$profiles = [];
		foreach (new \DirectoryIterator($profileDir) as $file) {
			if ($file->isFile() && $loader->supports($file->getFilename())) {
				$profiles[] = $file->getFilename();
			}
		}
		
		return $profiles;
	}
	
	public function getConfig() {
		return $this->config;
	}
	
	public function getIndentation($key) {
		if (isset($this->config['indentation'][$key])) {
			return $this->config['indentation'][$key];
		}
	}
	
	public function getBraces($key) {
		if (isset($this->config['braces'][$key])) {
			return $this->config['braces'][$key];
		}
	}
	
	public function getWhitespace($key, $context = 'default') {
		if (isset($this->config['whitespace'][$context][$key])) {
			$val = $this->config['whitespace'][$context][$key];
			
			if ($val === 'default' && $context !== 'default') {
				return $this->getWhitespace($key);
			}
			return $val;
		} else if ($context !== 'default') { // workaround?
			return $this->getWhitespace($key);
		}
		return false;
	}
	
	public function getBlanks($key) {
		if (isset($this->config['blanks'][$key])) {
			return $this->config['blanks'][$key];
		}
		return 0;
	}
	
	public function getNewline($key) {
		if (isset($this->config['newlines'][$key])) {
			return $this->config['newlines'][$key];
		}
	}
}
