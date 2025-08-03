<?php
declare(strict_types=1);

namespace Mail\Mail;

use Common\Util\Encoding;
use DateTime;
use Mail\Db\MailEntity;
use Mail\Db\MailEntitySaver;
use Mail\Db\RecipientEntity;
use Mail\Mail\Attachment\FileSystemHandler;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use Throwable;

readonly class Sender
{
	private array $mailConfig;

	public function __construct(
		private array $config,
		private MailEntitySaver $saver,
		private FileSystemHandler $attachmentFileSystemHandler
	)
	{
	}

	/**
	 * @throws Throwable
	 */
	public function send(MailEntity $mailEntity): bool
	{
		$this->mailConfig = $this->config['mail'];

		try
		{
			$dsn = $this->mailConfig['dsn'] ?? null;

			if (!$dsn) // fallback for laminas-mail style config
			{
				$transportOptions = $this->mailConfig['smtp'] ?? [];
				$connectionConfig = $transportOptions['connection_config'] ?? [];

				if (
					($username = $connectionConfig['username'] ?? null)
					&& ($password = $connectionConfig['password'] ?? null)
				)
				{
					$dsn = sprintf(
						'smtp://%s:%s@%s:%s',
						$username,
						$password,
						$transportOptions['host'],
						$transportOptions['port']
					);
				}
				else
				{
					$dsn = sprintf(
						'smtp://%s:%s',
						$transportOptions['host'],
						$transportOptions['port']
					);
				}

				// TODO encryption tls?
			}

			$transport = Transport::fromDsn($dsn);

			$from = $mailEntity->getFrom();

			$email = (new Email())
				->subject(
					$this->isDebugEnabled()
						? 'DEBUG: ' . $mailEntity->getSubject()
						: $mailEntity->getSubject()
				)
				->from(
					new Address(
						$from->getEmail(),
						$from->getName() ?? ''
					)
				)
				->html($mailEntity->getBody(), 'utf-8');

			// Reply-To
			if (($replyTo = $mailEntity->getReplyTo()))
			{
				$email->replyTo(
					new Address($replyTo->getEmail(), $replyTo->getName() ?? '')
				);
			}

			$this->loadRecipients($mailEntity, $email);

			foreach ($mailEntity->getAttachments() as $attachmentEntity)
			{
				$content = $this->attachmentFileSystemHandler->read($attachmentEntity);
				if (!$content)
				{
					continue;
				}

				$filename = Encoding::utf8Decode(
					$attachmentEntity->getName() . '.' . $attachmentEntity->getExtension()
				);

				$email->addPart(
					new DataPart(
						$content,
						$filename,
						$attachmentEntity->getMimeType(),
						'base64'
					)
				);
			}

			$transport->send($email);

			$mailEntity->setSentAt(new DateTime());
			$this->saver->save($mailEntity);

			return true;
		}
		catch (Throwable $ex)
		{
			$mailEntity->setError($ex->getMessage());
			$this->saver->save($mailEntity);
		}

		return false;
	}

	private function loadRecipients(MailEntity $mailEntity, Email $email): void
	{
		if ($this->isDebugEnabled())
		{
			$email->to($this->mailConfig['debug']['email']);
			return;
		}

		$to  = [];
		$cc  = [];
		$bcc = [];

		foreach ($mailEntity->getRecipients() as $recipient)
		{
			$address = new Address($recipient->getEmail(), $recipient->getName() ?? '');

			switch ($recipient->getType())
			{
				case RecipientEntity::TYPE_TO:
					$to[] = $address;
					break;
				case RecipientEntity::TYPE_CC:
					$cc[] = $address;
					break;
				case RecipientEntity::TYPE_BCC:
					$bcc[] = $address;
					break;
			}
		}

		if ($to)
		{
			$email->to(...$to);
		}

		if ($cc)
		{
			$email->cc(...$cc);
		}

		if ($bcc)
		{
			$email->bcc(...$bcc);
		}
	}

	private function isDebugEnabled(): bool
	{
		return $this->mailConfig['debug']['enabled'] ?? false;
	}
}
