<?php
declare(strict_types=1);

namespace Mail\Db;

use Common\Db\Entity;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
#[ORM\Table(name: 'mail_recipients')]
class RecipientEntity implements Entity
{
	public const int TYPE_TO  = 1;
	public const int TYPE_CC  = 2;
	public const int TYPE_BCC = 3;

	#[ORM\Id]
	#[ORM\Column(type: 'uuid')]
	private UuidInterface $id;

	#[ORM\Column(type: 'string')]
	private string $email;

	#[ORM\Column(type: 'string', nullable: true)]
	private ?string $name = null;

	#[ORM\Column(type: 'integer')]
	private int $type;

	#[ORM\ManyToOne(
		targetEntity: MailEntity::class,
		cascade: [ 'persist' ],
		inversedBy: 'recipients'
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

	public function getEmail(): string
	{
		return $this->email;
	}

	public function setEmail(string $email): void
	{
		$this->email = $email;
	}

	public function getName(): ?string
	{
		return $this->name;
	}

	public function setName(?string $name): void
	{
		$this->name = $name;
	}

	public function getType(): int
	{
		return $this->type;
	}

	public function setType(int $type): void
	{
		$this->type = $type;
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
