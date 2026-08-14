<?php

use Doctrine\DBAL\Driver\PDO\SQLite\Driver;
use Ramsey\Uuid\Doctrine\UuidType;

return [

	'templates' => [
		'paths' => [
			'testing' => [ __DIR__ . '/../../test/view' ],
		],
	],

	'doctrine' => [
		'configuration' => [
			'orm_default' => [
				'proxy_dir' => __DIR__ . '/../../data/DoctrineORMModule/Proxy',
			],
		],
		'connection'    => [
			'orm_default' => [
				'params' => [
					'driverClass' => Driver::class,
					'driver'      => 'pdo_sqlite',
					'path'        => __DIR__ . '/../../data/testing/test.sqlite',
				],
			],
		],
		'types'         => [
			UuidType::NAME => UuidType::class,
		],
	],
	'mail'     => [
		'dsn'        => 'null://null', // nothing is really sent while testing
		'attachment' => [
			'storeDirectory' => __DIR__ . '/../../data/testing/attachments',
		],
	],
];