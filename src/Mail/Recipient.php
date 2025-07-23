<?php
declare(strict_types=1);

namespace Mail\Mail;

class Recipient
{
	private string $email;

	/**
	 * @var string|null
	 */
	private ?string $name;

	private function __construct(
		$email,
		$name = null
	)
	{
		$this->email = $email;
		$this->name = $name;
	}

	public static function create(string $email, string $name = null): self
	{
		return new self($email, $name);
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
}