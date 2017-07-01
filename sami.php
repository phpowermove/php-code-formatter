<?php
require_once __DIR__ . '/vendor/autoload.php';

use Sami\Sami;
use Sami\Version\GitVersionCollection;
use Symfony\Component\Finder\Finder;

$dir = __DIR__ . '/src';
$iterator = Finder::create()
	->files()
	->name('*.php')
	->in($dir)
;

return new Sami($iterator, [
	'title' => 'PHP Code Formatter API',
	'theme' => 'default',
	'build_dir' => __DIR__ . '/api/%version%',
	'cache_dir' => __DIR__ . '/cache/%version%',
	'default_opened_level' => 2
]);
