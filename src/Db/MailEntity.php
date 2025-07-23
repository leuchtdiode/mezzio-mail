<?php
declare(strict_types=1);

namespace Mail\Db;

use Common\Db\Entity;
use DateTime;
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
	private DateTime $createdAt;

	#[ORM\Column(type: 'datetime', nullable: true)]
	private ?DateTime $sentAt = null;

	#[ORM\Column(type: 'string', nullable: true)]
	private ?string $error = null;

	/**
	 * @var Collection|RecipientEntity[]
	 **/
	#[ORM\OneToMany(
		mappedBy: 'mail',
		targetEntity: RecipientEntity::class,
		cascade: [ 'all'],
		orphanRemoval: true
	)]
	private Collection|array $recipients;

	#[ORM\OneToOne(
		mappedBy: 'mail',
		targetEntity: FromEntity::class,
		cascade: [ 'all'],
		orphanRemoval: true
	)]
	private FromEntity $from;

	#[ORM\OneToOne(
		mappedBy: 'mail',
		targetEntity: ReplyToEntity::class,
		cascade: [ 'all'],
		orphanRemoval: true
	)]
	private ?ReplyToEntity $replyTo = null;

	/**
	 * @var Collection|AttachmentEntity[]
	 **/
	#[ORM\OneToMany(
		mappedBy: 'mail',
		targetEntity: AttachmentEntity::class,
		cascade: [ 'all'],
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

	public function getCreatedAt(): DateTime
	{
		return $this->createdAt;
	}

	public function setCreatedAt(DateTime $createdAt): void
	{
		$this->createdAt = $createdAt;
	}

	public function getSentAt(): ?DateTime
	{
		return $this->sentAt;
	}

	public function setSentAt(?DateTime $sentAt): void
	{
		$this->sentAt = $sentAt;
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
