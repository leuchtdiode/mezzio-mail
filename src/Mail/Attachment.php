<?php
declare(strict_types=1);

namespace Mail\Mail;

class Attachment
{
	private string $fileName;

	private string $mimeType;

	private string $content;

	public static function create(): self
	{
		return new self();
	}

	public function getFileName(): string
	{
		return $this->fileName;
	}

	public function setFileName(string $fileName): Attachment
	{
		$this->fileName = $fileName;
		return $this;
	}

	public function getMimeType(): string
	{
		return $this->mimeType;
	}

	public function setMimeType(string $mimeType): Attachment
	{
		$this->mimeType = $mimeType;
		return $this;
	}

	public function getContent(): string
	{
		return $this->content;
	}

	public function setContent(string $content): Attachment
	{
		$this->content = $content;
		return $this;
	}
}