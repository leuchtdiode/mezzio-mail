<?php
declare(strict_types=1);

namespace Mail\Mail;

use Common\Util\Encoding;
use DateTime;
use Exception;
use Laminas\Mail\AddressList;
use Laminas\Mail\Message as MailMessage;
use Laminas\Mail\Transport\Smtp;
use Laminas\Mail\Transport\SmtpOptions;
use Laminas\Mime\Message as MimeMessage;
use Laminas\Mime\Mime;
use Laminas\Mime\Part;
use Mail\Db\MailEntity;
use Mail\Db\MailEntitySaver;
use Mail\Db\RecipientEntity;
use Mail\Mail\Attachment\FileSystemHandler;
use Throwable;

class Sender
{
	private array $config;

	private MailEntitySaver $saver;

	private FileSystemHandler $attachmentFileSystemHandler;

	private AddressList $to;

	private AddressList $cc;

	private AddressList $bcc;

	private array $mailConfig;

	public function __construct(array $config, MailEntitySaver $saver, FileSystemHandler $attachmentFileSystemHandler)
	{
		$this->config                      = $config;
		$this->saver                       = $saver;
		$this->attachmentFileSystemHandler = $attachmentFileSystemHandler;
	}

	/**
	 * @throws Throwable
	 */
	public function send(MailEntity $mailEntity): bool
	{
		$this->mailConfig = $this->config['mail'];

		try
		{
			$options = new SmtpOptions($this->mailConfig['smtp']);

			$transport = new Smtp($options);

			$text          = new Part($mailEntity->getBody());
			$text->type    = Mime::TYPE_HTML;
			$text->charset = 'utf-8';

			$mailParts = [
				$text,
			];

			$from = $mailEntity->getFrom();

			$message = new MailMessage();
			$message->setEncoding('UTF-8');
			$message->setSubject(
				$this->isDebugEnabled()
					? 'DEBUG: ' . $mailEntity->getSubject()
					: $mailEntity->getSubject()
			);
			$message->setFrom(
				$from->getEmail(),
				$from->getName()
			);

			if (($replyTo = $mailEntity->getReplyTo()))
			{
				$message->setReplyTo(
					$replyTo->getEmail(),
					$replyTo->getName()
				);
			}
			else
			{
				$message->setReplyTo($from->getEmail());
			}

			$this->loadRecipients($mailEntity);

			$message->setTo($this->to);
			$message->setCc($this->cc);
			$message->setBcc($this->bcc);

			foreach ($mailEntity->getAttachments() as $attachmentEntity)
			{
				$content = $this->attachmentFileSystemHandler->read($attachmentEntity);

				if (!$content)
				{
					continue;
				}

				$attachment = new Part($content);
				$attachment->setType(
					$attachmentEntity->getMimeType()
				);
				$attachment->setFileName(
					Encoding::utf8Decode($attachmentEntity->getName() . '.' . $attachmentEntity->getExtension())
				);
				$attachment->setDisposition(Mime::DISPOSITION_ATTACHMENT);
				$attachment->setEncoding(Mime::ENCODING_BASE64);
				$attachment->setCharset('UTF-8');
				$attachment->setId($attachmentEntity->getId()
					->toString());

				$mailParts[] = $attachment;
			}

			$mimeMessage = new MimeMessage();
			$mimeMessage->setParts($mailParts);

			$message->setBody($mimeMessage);

			$transport->send($message);

			$mailEntity->setSentAt(new DateTime());

			$this->saver->save($mailEntity);

			return true;
		}
		catch (Exception $ex)
		{
			$mailEntity->setError($ex->getMessage());

			$this->saver->save($mailEntity);
		}

		return false;
	}

	private function loadRecipients(MailEntity $mailEntity): void
	{
		$this->to  = new AddressList();
		$this->cc  = new AddressList();
		$this->bcc = new AddressList();

		if ($this->isDebugEnabled())
		{
			$this->to->add(
				$this->mailConfig['debug']['email']
			);

			return;
		}

		foreach ($mailEntity->getRecipients() as $recipient)
		{
			switch ($recipient->getType())
			{
				case RecipientEntity::TYPE_TO:
					$this->to->add($recipient->getEmail(), $recipient->getName());
					break;

				case RecipientEntity::TYPE_CC:
					$this->cc->add($recipient->getEmail(), $recipient->getName());
					break;

				case RecipientEntity::TYPE_BCC:
					$this->bcc->add($recipient->getEmail(), $recipient->getName());
					break;
			}
		}
	}

	private function isDebugEnabled(): bool
	{
		return $this->mailConfig['debug']['enabled'] ?? false;
	}
}
