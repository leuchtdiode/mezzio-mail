<?php
namespace MailTest\Db;

use DateTime;
use Mail\Db\FromEntity;
use Mail\Db\MailEntity;
use Mail\Db\MailEntityRepository;
use Mail\Db\MailEntitySaver;
use Testing\BaseTestCase;
use Throwable;

class MailEntityRepositoryTest extends BaseTestCase
{
	private MailEntityRepository $repository;

	private MailEntitySaver $saver;

	/**
	 * @throws Throwable
	 */
	public function setUp(): void
	{
		parent::setUp();

		$this->repository = $this->getInstance(MailEntityRepository::class);
		$this->saver      = $this->getInstance(MailEntitySaver::class);
	}

	/**
	 * @throws Throwable
	 */
	public function test_mail_can_only_be_claimed_once()
	{
		$mail = $this->createMail();

		$this->assertTrue($this->repository->claim($mail));
		$this->assertNotNull($mail->getProcessingAt());

		// a second cron or worker must not get the same mail, it would be sent twice
		$this->assertFalse($this->repository->claim($mail));
	}

	/**
	 * @throws Throwable
	 */
	public function test_sent_mail_can_not_be_claimed()
	{
		$mail = $this->createMail();
		$mail->setSentAt(new DateTime());

		$this->saver->save($mail);

		$this->assertFalse($this->repository->claim($mail));
		$this->assertNull($mail->getProcessingAt());
	}

	/**
	 * @throws Throwable
	 */
	public function test_failed_mail_can_not_be_claimed()
	{
		$mail = $this->createMail();
		$mail->setError('something went wrong');

		$this->saver->save($mail);

		$this->assertFalse($this->repository->claim($mail));
		$this->assertNull($mail->getProcessingAt());
	}

	/**
	 * @throws Throwable
	 */
	private function createMail(): MailEntity
	{
		$mail = new MailEntity();
		$mail->setSubject('test betreff');
		$mail->setBody('test body');

		$from = new FromEntity();
		$from->setEmail('from@anything.com');
		$from->setName('Absender');
		$from->setMail($mail);

		$mail->setFrom($from);

		$this->saver->save($mail);

		return $mail;
	}
}
