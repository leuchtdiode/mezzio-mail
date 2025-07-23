<?php

use Ramsey\Uuid\Doctrine\UuidType;
use Doctrine\DBAL\Driver\PDO\SQLite\Driver;

return [
	'view_manager' => [
		'template_map'        => [
			'testing/mail/test-layout'   => __DIR__ . '/../../test/view/test-layout.phtml',
			'testing/mail/test-template' => __DIR__ . '/../../test/view/test-template.phtml'
		],
		'template_path_stack' => [
			__DIR__ . '/../../test/view',
		],
	],
	'doctrine'     => [
		'configuration' => [
			'orm_default' => [
				'proxy_dir' => __DIR__ . '/../../data/DoctrineORMModule/Proxy',
				'types'     => [
					UuidType::NAME => UuidType::class,
				],
			],
		],
		'connection'    => [
			'orm_default' => [
				'params' => [
					'driverClass' => Driver::class,
					'driver'      => 'pdo_sqlite',
					'path'        => __DIR__ . '/../../data/testing/test.sqlite',
				],
			]
		]
	],
	'mail' => [
		'attachment' => [
			'storeDirectory' => __DIR__ . '/../../data/testing/attachments'
		],
	],
];