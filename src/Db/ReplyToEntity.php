<?php
declare(strict_types=1);

namespace Mail\Db;

use Common\Db\Entity;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
#[ORM\Table(name: 'mail_reply_to')]
class ReplyToEntity implements Entity
{
	#[ORM\Id]
	#[ORM\Column(type: 'uuid')]
	private UuidInterface $id;

	#[ORM\Column(type: 'string')]
	private string $email;

	#[ORM\Column(type: 'string', nullable: true)]
	private ?string $name = null;

	#[ORM\OneToOne(
		inversedBy: 'replyTo',
		targetEntity: MailEntity::class,
		cascade: [ 'persist' ]
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

	public function getMail(): MailEntity
	{
		return $this->mail;
	}

	public function setMail(MailEntity $mail): void
	{
		$this->mail = $mail;
	}
}
