<?php
declare(strict_types=1);

namespace Mail\Mail\Attachment;

use Exception;
use function file_exists;
use function file_get_contents;
use Mail\Db\Attachment\Entity;

class FileSystemHandler
{
	private array $config;

	public function __construct(array $config)
	{
		$this->config = $config;
	}

	/**
	 * @throws Exception
	 */
	public function write(Entity $entity, string $content): bool
	{
		$this->ensureDirectoryOrFail();

		$path = $this->getPath($entity);

		return file_put_contents($path, $content) !== false;
	}

	public function read(Entity $entity): ?string
	{
		$path = $this->getPath($entity);

		if (!file_exists($path))
		{
			return null;
		}

		return file_get_contents($path);
	}

	private function getDirectory(): ?string
	{
		return $this->config['mail']['attachment']['storeDirectory'] ?? null;
	}

	/**
	 * @throws Exception
	 */
	private function ensureDirectoryOrFail(): void
	{
		$directory = $this->getDirectory();

		if (!$directory || !file_exists($directory) || !is_writable($directory))
		{
			throw new Exception('Attachment store directory ' . $directory . ' does not exist or is not writable');
		}
	}

	private function getPath(Entity $entity): string
	{
		return sprintf(
			'%s/%s.%s',
			$this->getDirectory(),
			$entity->getId(),
			$entity->getExtension()
		);
	}
}