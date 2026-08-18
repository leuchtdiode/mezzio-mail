<?php
declare(strict_types=1);

namespace Mail\Queue;

use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use Mail\Db\Attachment\Entity as AttachmentEntity;
use Mail\Db\FromEntity;
use Mail\Db\MailEntity;
use Mail\Db\MailEntityRepository;
use Mail\Db\MailEntitySaver;
use Mail\Db\RecipientEntity;
use Mail\Db\ReplyToEntity;
use Mail\Mail\Attachment\FileSystemHandler;
use Mail\Mail\BodyCreator;
use Mail\Mail\Mail;
use Mail\Mail\Recipient;
use Mail\Mail\Sender;
use Throwable;

class Queue
{
	public function __construct(
		private readonly BodyCreator $bodyCreator,
		private readonly MailEntitySaver $saver,
		private readonly MailEntityRepository $repository,
		private readonly FileSystemHandler $attachmentFileSystemHandler,
		private readonly Sender $sender
	)
	{
	}

	/**
	 * @throws Throwable
	 */
	public function add(Mail $mail): void
	{
		$body = $this->bodyCreator->forMail($mail);

		$mailEntity = new MailEntity();

		$mailEntity->setRecipients(
			new ArrayCollection(
				$this->makeRecipients($mail, $mailEntity)
			)
		);
		$mailEntity->setFrom(
			$this->makeFrom($mail, $mailEntity)
		);
		$mailEntity->setReplyTo(
			$this->makeReplyTo($mail, $mailEntity)
		);
		$mailEntity->setSubject($mail->getSubject());
		$mailEntity->setBody($body);

		$this->makeAttachments($mail, $mailEntity);

		$this->saver->save($mailEntity);

		// claim atomically, a cron or worker may have picked the mail up between saving and sending it
		if ($mail->isSendImmediately() && $this->repository->claim($mailEntity))
		{
			$this->sender->send($mailEntity);
		}
	}

	private function makeFrom(Mail $mail, MailEntity $mailEntity): FromEntity
	{
		$mailFrom = $mail->getFrom();

		$fromEntity = new FromEntity();
		$fromEntity->setEmail($mailFrom->getEmail());
		$fromEntity->setName($mailFrom->getName());
		$fromEntity->setMail($mailEntity);

		return $fromEntity;
	}

	private function makeReplyTo(Mail $mail, MailEntity $mailEntity): ?ReplyToEntity
	{
		$replyTo = $mail->getReplyTo();

		if (!$replyTo)
		{
			return null;
		}

		$entity = new ReplyToEntity();
		$entity->setEmail($replyTo->getEmail());
		$entity->setName($replyTo->getName());
		$entity->setMail($mailEntity);

		return $entity;
	}

	/**
	 * @return RecipientEntity[]
	 */
	private function makeRecipients(Mail $mail, MailEntity $mailEntity): array
	{
		return array_merge(
			$this->makeRecipientsForType($mail->getTo(), RecipientEntity::TYPE_TO, $mailEntity),
			$this->makeRecipientsForType($mail->getCc(), RecipientEntity::TYPE_CC, $mailEntity),
			$this->makeRecipientsForType($mail->getBcc(), RecipientEntity::TYPE_BCC, $mailEntity)
		);
	}

	/**
	 * @param Recipient[] $recipients
	 * @return RecipientEntity[]
	 */
	private function makeRecipientsForType(array $recipients, int $type, MailEntity $mailEntity): array
	{
		$entities = [];

		foreach ($recipients as $recipient)
		{
			$recipientEntity = new RecipientEntity();
			$recipientEntity->setEmail($recipient->getEmail());
			$recipientEntity->setName($recipient->getName());
			$recipientEntity->setType($type);
			$recipientEntity->setMail($mailEntity);

			$entities[] = $recipientEntity;
		}

		return $entities;
	}

	/**
	 * @throws Exception
	 */
	private function makeAttachments(Mail $mail, MailEntity $mailEntity): void
	{
		foreach ($mail->getAttachments() as $attachment)
		{
			$fileName = $attachment->getFileName();

			$attachmentEntity = new AttachmentEntity();
			$attachmentEntity->setMail($mailEntity);
			$attachmentEntity->setMimeType($attachment->getMimeType());
			$attachmentEntity->setName(
				pathinfo($fileName, PATHINFO_FILENAME)
			);
			$attachmentEntity->setExtension(
				pathinfo($fileName, PATHINFO_EXTENSION)
			);

			$mailEntity
				->getAttachments()
				->add($attachmentEntity);

			$this->attachmentFileSystemHandler->write($attachmentEntity, $attachment->getContent());
		}
	}
}
