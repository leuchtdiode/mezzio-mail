<?php
declare(strict_types=1);

namespace Mail\Mail\Attachment;

use Common\Util\Encoding;
use Mail\Db\Attachment\Entity;
use Symfony\Component\Mime\Part\DataPart;

/**
 * Makes the mime part of a stored attachment, read either from the attachment store or, when it was
 * added by path, from that file. An inline attachment gets its content id, so the html can refer to it
 * with src="cid:<contentId>".
 */
class PartFactory
{
	public function __construct(
		private readonly FileSystemHandler $fileSystemHandler
	)
	{
	}

	/**
	 * null when there is no content (anymore), the mail is sent without the attachment then
	 */
	public function create(Entity $entity): ?DataPart
	{
		$content = $this->read($entity);

		if (!$content)
		{
			return null;
		}

		$part = new DataPart(
			$content,
			Encoding::utf8Decode(
				$entity->getName() . '.' . $entity->getExtension()
			),
			$entity->getMimeType(),
			'base64'
		);

		if (($contentId = $entity->getContentId()))
		{
			$part
				->asInline()
				->setContentId($contentId);
		}

		return $part;
	}

	private function read(Entity $entity): ?string
	{
		$filePath = $entity->getFilePath();

		if ($filePath === null)
		{
			return $this->fileSystemHandler->read($entity);
		}

		if (!is_readable($filePath))
		{
			return null;
		}

		$content = file_get_contents($filePath);

		return $content === false ? null : $content;
	}
}
