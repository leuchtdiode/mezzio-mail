<?php
declare(strict_types=1);

namespace Mail\Mail;

class Mail
{
	/**
	 * @var Recipient[]
	 */
	private array $to = [];

	/**
	 * @var Recipient[]
	 */
	private array $cc = [];

	/**
	 * @var Recipient[]
	 */
	private array $bcc = [];

	private Recipient $from;

	private ?Recipient $replyTo = null;

	private string $subject;

	private string $layoutTemplate;

	private string $contentTemplate;

	private ?PlaceholderValues $placeholderValues = null;

	/**
	 * @var Attachment[]
	 */
	private array $attachments = [];

	public function addTo(Recipient $recipient): void
	{
		$this->to[] = $recipient;
	}

	public function addCc(Recipient $recipient): void
	{
		$this->cc[] = $recipient;
	}

	public function addBcc(Recipient $recipient): void
	{
		$this->bcc[] = $recipient;
	}

	public function addAttachment(Attachment $attachment): void
	{
		$this->attachments[] = $attachment;
	}

	/**
	 * @param Recipient[] $to
	 */
	public function setTo(array $to): void
	{
		$this->to = $to;
	}

	/**
	 * @param Recipient[] $cc
	 */
	public function setCc(array $cc): void
	{
		$this->cc = $cc;
	}

	/**
	 * @param Recipient[] $bcc
	 */
	public function setBcc(array $bcc): void
	{
		$this->bcc = $bcc;
	}

	public function setFrom(Recipient $from): void
	{
		$this->from = $from;
	}

	public function setReplyTo(?Recipient $replyTo): void
	{
		$this->replyTo = $replyTo;
	}

	public function setSubject(string $subject): void
	{
		$this->subject = $subject;
	}

	public function setLayoutTemplate(string $layoutTemplate): void
	{
		$this->layoutTemplate = $layoutTemplate;
	}

	public function setContentTemplate(string $contentTemplate): void
	{
		$this->contentTemplate = $contentTemplate;
	}

	/**
	 * @return Recipient[]
	 */
	public function getTo(): array
	{
		return $this->to;
	}

	/**
	 * @return Recipient[]
	 */
	public function getCc(): array
	{
		return $this->cc;
	}

	/**
	 * @return Recipient[]
	 */
	public function getBcc(): array
	{
		return $this->bcc;
	}

	public function getFrom(): Recipient
	{
		return $this->from;
	}

	public function getReplyTo(): ?Recipient
	{
		return $this->replyTo;
	}

	public function getSubject(): string
	{
		return $this->subject;
	}

	public function getLayoutTemplate(): string
	{
		return $this->layoutTemplate;
	}

	public function getContentTemplate(): string
	{
		return $this->contentTemplate;
	}

	public function getPlaceholderValues(): ?PlaceholderValues
	{
		return $this->placeholderValues;
	}

	public function setPlaceholderValues(?PlaceholderValues $placeholderValues): void
	{
		$this->placeholderValues = $placeholderValues;
	}

	/**
	 * @return Attachment[]
	 */
	public function getAttachments(): array
	{
		return $this->attachments;
	}

	/**
	 * @param Attachment[] $attachments
	 */
	public function setAttachments(array $attachments): void
	{
		$this->attachments = $attachments;
	}
}