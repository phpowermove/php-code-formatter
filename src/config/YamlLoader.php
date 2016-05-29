<?php
namespace gossi\formatter\config;

use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Yaml\Yaml;

class YamlLoader extends FileLoader {

	public function load($resource, $type = null) {
		return Yaml::parse(file_get_contents($resource));
	}

	public function supports($resource, $type = null) {
		return is_string($resource)
			&& in_array(pathinfo($resource, PATHINFO_EXTENSION), ['yml', 'yaml']);
	}
}
