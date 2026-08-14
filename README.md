# mezzio-mail

Mezzio module for handling mails

## Queue

`mail:queue:send` sends the mails waiting in the queue, either once per call or, with `--worker`, in a loop
until a shutdown is requested or `--max-runtime` is exceeded.

Every mail is claimed atomically before it is sent, so it is safe to run several crons or workers next to
each other. The claim is a single `UPDATE` which only hits the mail as long as it is unsent, has no error
and is not claimed yet:

```sql
UPDATE mail_mails SET processingAt = ... WHERE id = ... AND processingAt IS NULL AND sentAt IS NULL AND error IS NULL
```

Only the one which got the row sends the mail, all others skip it and go on with the next one. Mails sent
immediately (`Mail::setSendImmediately()`) are claimed as well, so a worker which picked the mail up in the
meantime does not send it a second time.

The claim is never released. If a mail can not be sent, the error is stored and it is not picked up again.
If the process dies while sending, the mail stays claimed on purpose, because it may have been sent already
&mdash; the health check below reports it, so it can be looked at instead of being delivered twice.

The `processingAt` column is new, so a schema migration is necessary when updating from an earlier version.

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
