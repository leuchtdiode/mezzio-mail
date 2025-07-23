<?php
declare(strict_types=1);

namespace Mail\Queue;

use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use Mail\Db\Attachment\Entity as AttachmentEntity;
use Mail\Db\FromEntity;
use Mail\Db\MailEntity;
use Mail\Db\MailEntitySaver;
use Mail\Db\RecipientEntity;
use Mail\Db\ReplyToEntity;
use Mail\Mail\Attachment\FileSystemHandler;
use Mail\Mail\BodyCreator;
use Mail\Mail\Mail;
use Mail\Mail\Recipient;
use Throwable;

class Queue
{
	private BodyCreator $bodyCreator;

	private MailEntitySaver $saver;

	private FileSystemHandler $attachmentFileSystemHandler;

	private Mail $mail;

	private MailEntity $mailEntity;

	/**
	 * @var RecipientEntity[]
	 */
	private array $recipients;

	public function __construct(
		BodyCreator $bodyCreator,
		MailEntitySaver $saver,
		FileSystemHandler $attachmentFileSystemHandler
	)
	{
		$this->bodyCreator                 = $bodyCreator;
		$this->saver                       = $saver;
		$this->attachmentFileSystemHandler = $attachmentFileSystemHandler;
	}

	/**
	 * @throws Throwable
	 */
	public function add(Mail $mail): void
	{
		$this->mail = $mail;

		$body = $this->bodyCreator->forMail($mail);

		$this->mailEntity = new MailEntity();

		$this->makeRecipients();

		$this->mailEntity->setRecipients(
			new ArrayCollection($this->recipients)
		);
		$this->mailEntity->setFrom(
			$this->makeFrom()
		);
		$this->mailEntity->setReplyTo(
			$this->makeReplyTo()
		);
		$this->mailEntity->setSubject($mail->getSubject());
		$this->mailEntity->setBody($body);

		$this->makeAttachments();

		$this->saver->save($this->mailEntity);
	}

	private function makeFrom(): FromEntity
	{
		$mailFrom = $this->mail->getFrom();

		$fromEntity = new FromEntity();
		$fromEntity->setEmail($mailFrom->getEmail());
		$fromEntity->setName($mailFrom->getName());
		$fromEntity->setMail($this->mailEntity);

		return $fromEntity;
	}

	private function makeReplyTo(): ?ReplyToEntity
	{
		$replyTo = $this->mail->getReplyTo();

		if (!$replyTo)
		{
			return null;
		}

		$entity = new ReplyToEntity();
		$entity->setEmail($replyTo->getEmail());
		$entity->setName($replyTo->getName());
		$entity->setMail($this->mailEntity);

		return $entity;
	}

	private function makeRecipients(): void
	{
		$this->addRecipientsForType($this->mail->getTo(), RecipientEntity::TYPE_TO);
		$this->addRecipientsForType($this->mail->getCc(), RecipientEntity::TYPE_CC);
		$this->addRecipientsForType($this->mail->getBcc(), RecipientEntity::TYPE_BCC);
	}

	/**
	 * @param Recipient[] $recipients
	 */
	private function addRecipientsForType(array $recipients, int $type): void
	{
		if (!$recipients)
		{
			return;
		}

		foreach ($recipients as $recipient)
		{
			$recipientEntity = new RecipientEntity();
			$recipientEntity->setEmail($recipient->getEmail());
			$recipientEntity->setName($recipient->getName());
			$recipientEntity->setType($type);
			$recipientEntity->setMail($this->mailEntity);

			$this->recipients[] = $recipientEntity;
		}
	}

	/**
	 * @throws Exception
	 */
	private function makeAttachments(): void
	{
		foreach ($this->mail->getAttachments() as $attachment)
		{
			$fileName = $attachment->getFileName();

			$attachmentEntity = new AttachmentEntity();
			$attachmentEntity->setMail($this->mailEntity);
			$attachmentEntity->setMimeType($attachment->getMimeType());
			$attachmentEntity->setName(
				pathinfo($fileName, PATHINFO_FILENAME)
			);
			$attachmentEntity->setExtension(
				pathinfo($fileName, PATHINFO_EXTENSION)
			);

			$this->mailEntity
				->getAttachments()
				->add($attachmentEntity);

			$this->attachmentFileSystemHandler->write($attachmentEntity, $attachment->getContent());
		}
	}
}
