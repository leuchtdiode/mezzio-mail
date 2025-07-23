<?php

$appConfig = [];

$appConfig['modules']                                             = [
	'Laminas\Router',
	'Laminas\Cache',
	'Laminas\Cache\Storage\Adapter\Memory',
	'DoctrineModule',
	'DoctrineORMModule',
	'Mail',
];
$appConfig['module_listener_options']['module_paths']             = [
	__DIR__ . '/../module',
	__DIR__ . '/../vendor',
];
$appConfig['module_listener_options']['config_cache_enabled']     = false;
$appConfig['module_listener_options']['module_map_cache_enabled'] = false;

// important not to load local.php!!
$appConfig['module_listener_options']['config_glob_paths'] = [
	__DIR__ . '/autoload/{{,*.}global,{,*.}test}.php',
	__DIR__ . '/autoload/{,*.}{global,test}-development.php',
];

return $appConfig;
