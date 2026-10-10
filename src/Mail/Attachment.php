<?php
declare(strict_types=1);

namespace Mail\Mail;

use InvalidArgumentException;

class Attachment
{
	private string $fileName;

	private string $mimeType;

	private string $content = '';

	/**
	 * A file read when the mail is sent instead of a copy of its content, for files which are part of the
	 * application (a logo) and would otherwise be stored once per mail
	 */
	private ?string $filePath = null;

	/**
	 * Makes the attachment inline, the html refers to it with src="cid:<contentId>"
	 */
	private ?string $contentId = null;

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

	public function getFilePath(): ?string
	{
		return $this->filePath;
	}

	public function setFilePath(?string $filePath): Attachment
	{
		$this->filePath = $filePath;
		return $this;
	}

	public function getContentId(): ?string
	{
		return $this->contentId;
	}

	/**
	 * @throws InvalidArgumentException a content id without an @ is not accepted by symfony mime
	 */
	public function setContentId(?string $contentId): Attachment
	{
		if ($contentId !== null && !str_contains($contentId, '@'))
		{
			throw new InvalidArgumentException('Content id ' . $contentId . ' has to contain an @');
		}

		$this->contentId = $contentId;
		return $this;
	}

	public function isInline(): bool
	{
		return $this->contentId !== null;
	}
}
