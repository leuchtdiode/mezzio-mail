<?php
declare(strict_types=1);

namespace Mail\Db;

use Common\Db\Entity;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Mail\Db\Attachment\Entity as AttachmentEntity;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: MailEntityRepository::class)]
#[ORM\Table(name: 'mail_mails')]
class MailEntity implements Entity
{
	#[ORM\Id]
	#[ORM\Column(type: 'uuid')]
	private UuidInterface $id;

	#[ORM\Column(type: 'string')]
	private string $subject;

	#[ORM\Column(type: 'text')]
	private string $body;

	#[ORM\Column(type: 'datetime')]
	private DateTimeInterface $createdAt;

	#[ORM\Column(type: 'datetime', nullable: true)]
	private ?DateTimeInterface $sentAt = null;

	/**
	 * Set as soon as a cron or worker claimed the mail, so no other one picks it up and sends it a second time.
	 */
	#[ORM\Column(type: 'datetime', nullable: true)]
	private ?DateTimeInterface $processingAt = null;

	#[ORM\Column(type: 'string', length: 4000, nullable: true)]
	private ?string $error = null;

	/**
	 * @var Collection|RecipientEntity[]
	 **/
	#[ORM\OneToMany(
		targetEntity: RecipientEntity::class,
		mappedBy: 'mail',
		cascade: [ 'all' ],
		orphanRemoval: true
	)]
	private Collection|array $recipients;

	#[ORM\OneToOne(
		targetEntity: FromEntity::class,
		mappedBy: 'mail',
		cascade: [ 'all' ],
		orphanRemoval: true
	)]
	private FromEntity $from;

	#[ORM\OneToOne(
		targetEntity: ReplyToEntity::class,
		mappedBy: 'mail',
		cascade: [ 'all' ],
		orphanRemoval: true
	)]
	private ?ReplyToEntity $replyTo = null;

	/**
	 * @var Collection|AttachmentEntity[]
	 **/
	#[ORM\OneToMany(
		targetEntity: AttachmentEntity::class,
		mappedBy: 'mail',
		cascade: [ 'all' ],
		orphanRemoval: true
	)]
	private Collection|array $attachments;

	/**
	 * @throws Exception
	 */
	public function __construct()
	{
		$this->id          = Uuid::uuid4();
		$this->createdAt   = new DateTime();
		$this->recipients  = new ArrayCollection();
		$this->attachments = new ArrayCollection();
	}

	public function getId(): UuidInterface
	{
		return $this->id;
	}

	public function setId(UuidInterface $id): void
	{
		$this->id = $id;
	}

	public function getSubject(): string
	{
		return $this->subject;
	}

	public function setSubject(string $subject): void
	{
		$this->subject = $subject;
	}

	public function getBody(): string
	{
		return $this->body;
	}

	public function setBody(string $body): void
	{
		$this->body = $body;
	}

	public function getCreatedAt(): DateTimeInterface
	{
		return $this->createdAt;
	}

	public function setCreatedAt(DateTimeInterface $createdAt): void
	{
		$this->createdAt = $createdAt;
	}

	public function getSentAt(): ?DateTimeInterface
	{
		return $this->sentAt;
	}

	public function setSentAt(?DateTimeInterface $sentAt): void
	{
		$this->sentAt = $sentAt;
	}

	public function getProcessingAt(): ?DateTimeInterface
	{
		return $this->processingAt;
	}

	public function setProcessingAt(?DateTimeInterface $processingAt): void
	{
		$this->processingAt = $processingAt;
	}

	public function getError(): ?string
	{
		return $this->error;
	}

	public function setError(?string $error): void
	{
		$this->error = $error;
	}

	public function getRecipients(): array|ArrayCollection|Collection
	{
		return $this->recipients;
	}

	public function setRecipients(array|ArrayCollection|Collection $recipients): void
	{
		$this->recipients = $recipients;
	}

	public function getFrom(): FromEntity
	{
		return $this->from;
	}

	public function setFrom(FromEntity $from): void
	{
		$this->from = $from;
	}

	public function getReplyTo(): ?ReplyToEntity
	{
		return $this->replyTo;
	}

	public function setReplyTo(?ReplyToEntity $replyTo): void
	{
		$this->replyTo = $replyTo;
	}

	public function getAttachments(): ArrayCollection|Collection|array
	{
		return $this->attachments;
	}

	public function setAttachments(ArrayCollection|Collection|array $attachments): void
	{
		$this->attachments = $attachments;
	}
}
