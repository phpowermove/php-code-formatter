<?php
use gossi\formatter\Formatter;
use gossi\formatter\config\Config;

require_once __DIR__ . '/../vendor/autoload.php';

$config = new Config();
print_r($config->getConfig());