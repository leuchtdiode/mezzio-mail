<?php
namespace MailTest\Mail\Attachment;

use Mail\Db\Attachment\Entity;
use Mail\Mail\Attachment;
use Mail\Mail\Attachment\PartFactory;
use InvalidArgumentException;
use Testing\BaseTestCase;

class PartFactoryTest extends BaseTestCase
{
	private PartFactory $partFactory;

	public function setUp(): void
	{
		parent::setUp();

		$this->partFactory = $this->getInstance(PartFactory::class);
	}

	public function test_attachment_by_path_is_read_from_the_file()
	{
		$part = $this->partFactory->create(
			$this->makeEntity(__DIR__ . '/../../../files/logo.png', null)
		);

		$this->assertNotNull($part);
		$this->assertEquals('logo-bytes', $part->getBody());
		$this->assertEquals('attachment', $part->getDisposition());
	}

	public function test_inline_attachment_gets_its_content_id()
	{
		$part = $this->partFactory->create(
			$this->makeEntity(__DIR__ . '/../../../files/logo.png', 'logo@test')
		);

		$this->assertNotNull($part);
		$this->assertEquals('inline', $part->getDisposition());
		$this->assertTrue($part->hasContentId());
		$this->assertEquals('logo@test', $part->getContentId());
	}

	public function test_missing_file_gives_no_part()
	{
		$this->assertNull(
			$this->partFactory->create(
				$this->makeEntity(__DIR__ . '/does-not-exist.png', 'logo@test')
			)
		);
	}

	public function test_content_id_without_at_is_refused()
	{
		$this->expectException(InvalidArgumentException::class);

		Attachment::create()->setContentId('logo');
	}

	private function makeEntity(string $filePath, ?string $contentId): Entity
	{
		$entity = new Entity();
		$entity->setName('logo');
		$entity->setExtension('png');
		$entity->setMimeType('image/png');
		$entity->setFilePath($filePath);
		$entity->setContentId($contentId);

		return $entity;
	}
}
