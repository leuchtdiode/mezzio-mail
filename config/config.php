<?php

declare(strict_types=1);

use Laminas\ConfigAggregator\ArrayProvider;
use Laminas\ConfigAggregator\ConfigAggregator;
use Mail\ConfigProvider;
use MailTest\TestingConfigProvider;

// To enable or disable caching, set the `ConfigAggregator::ENABLE_CACHE` boolean in
// `config/autoload/local.php`.
$cacheConfig = [
	'config_cache_path' => 'data/cache/config-cache.php',
];

$providers = [
	new ArrayProvider($cacheConfig),
	\Laminas\I18n\ConfigProvider::class,
	\Laminas\Router\ConfigProvider::class,
	\Mezzio\LaminasView\ConfigProvider::class,
	ConfigProvider::class,
	\Common\ConfigProvider::class,
	\Mezzio\ConfigProvider::class,
	\Mezzio\Router\ConfigProvider::class,
];

if (getenv('APP_TESTING') !== false)
{
	$providers = array_merge($providers, [
		TestingConfigProvider::class,
		...\Trinet\MezzioTest\TestConfigProvider::load(),
	]);
}

$aggregator = new ConfigAggregator($providers, $cacheConfig['config_cache_path']);

return $aggregator->getMergedConfig();
