<?php
declare(strict_types=1);

namespace Mail\Db\Attachment;

use Doctrine\ORM\Mapping as ORM;
use Exception;
use Common\Db\Entity as DbEntity;
use Mail\Db\MailEntity;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: Repository::class)]
#[ORM\Table(name: 'mail_attachments')]
class Entity implements DbEntity
{
	#[ORM\Id]
	#[ORM\Column(type: 'uuid')]
	private UuidInterface $id;

	#[ORM\Column(type: 'string')]
	private string $name;

	#[ORM\Column(type: 'string')]
	private string $extension;

	#[ORM\Column(type: 'string')]
	private string $mimeType;

	/**
	 * set when the attachment is read from this path on sending instead of from the attachment store
	 */
	#[ORM\Column(type: 'string', nullable: true)]
	private ?string $filePath = null;

	/**
	 * set when the attachment is inline
	 */
	#[ORM\Column(type: 'string', nullable: true)]
	private ?string $contentId = null;

	#[ORM\ManyToOne(
		targetEntity: MailEntity::class,
		cascade: [ 'persist' ],
		inversedBy: 'attachments'
	)]
	#[ORM\JoinColumn(
		name: 'mailId',
		nullable: false,
		onDelete: 'CASCADE'
	)]
	private MailEntity $mail;

	/**
	 * @throws Exception
	 */
	public function __construct()
	{
		$this->id = Uuid::uuid4();
	}

	public function getId(): UuidInterface
	{
		return $this->id;
	}

	public function setId(UuidInterface $id): void
	{
		$this->id = $id;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function setName(string $name): void
	{
		$this->name = $name;
	}

	public function getExtension(): string
	{
		return $this->extension;
	}

	public function setExtension(string $extension): void
	{
		$this->extension = $extension;
	}

	public function getMimeType(): string
	{
		return $this->mimeType;
	}

	public function setMimeType(string $mimeType): void
	{
		$this->mimeType = $mimeType;
	}

	public function getFilePath(): ?string
	{
		return $this->filePath;
	}

	public function setFilePath(?string $filePath): void
	{
		$this->filePath = $filePath;
	}

	public function getContentId(): ?string
	{
		return $this->contentId;
	}

	public function setContentId(?string $contentId): void
	{
		$this->contentId = $contentId;
	}

	public function getMail(): MailEntity
	{
		return $this->mail;
	}

	public function setMail(MailEntity $mail): void
	{
		$this->mail = $mail;
	}
}