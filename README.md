# mezzio-mail

Mezzio module for handling mails

## Monitoring

`Mail\Health\UnsentMailsCheck` reports unhealthy as soon as a mail is queued but still unsent after a
configurable threshold, which means the queue is not processed anymore. Mails which failed with an error
are ignored, because those are never picked up again anyway.

It requires `leuchtdiode/mezzio-monitoring` and has to be registered in your application config:

```php
'monitoring' => [
	'health' => [
		'enabled'  => true,
		'checkers' => [
			UnsentMailsCheck::class,
		],
	],
],
```

The threshold defaults to 60 minutes and can be adapted:

```php
'mail' => [
	'monitoring' => [
		'unsentMails' => [
			'thresholdMinutes' => 60,
		],
	],
],
```
