<?php
namespace Mail;

use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Mail\Command\Queue\Send;

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

	'mail' => [
		'monitoring' => [
			'unsentMails' => [
				'thresholdMinutes' => 60,
			],
		],
	],
];