<?php
namespace Mail;

use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Mail\Command\Queue\Send;
use Mail\Health\UnsentMailsCheck;

return [

	'doctrine' => [
		'driver' => [
			'orm_default' => [
				'class' => AttributeDriver::class,
				'paths' => [ __DIR__ . '/../src/Db' ],
			],
		],
	],

	'console' => [
		'commands' => [
			Send::class,
		],
	],

	'dependencies' => [
		'abstract_factories' => [
			DefaultFactory::class,
		],
	],

	'monitoring' => [
		'health' => [
			'checkers' => [
				UnsentMailsCheck::class,
			],
		],
	],

	'mail' => [
		'monitoring' => [
			'unsentMails' => [
				'thresholdMinutes' => 60,
			],
		],
	],
];