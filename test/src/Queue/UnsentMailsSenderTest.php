<?php
namespace MailTest\Queue;

use Mail\Db\FromEntity;
use Mail\Db\MailEntity;
use Mail\Db\MailEntityRepository;
use Mail\Db\MailEntitySaver;
use Mail\Db\RecipientEntity;
use Mail\Queue\UnsentMailsSender;
use Testing\BaseTestCase;
use Throwable;

class UnsentMailsSenderTest extends BaseTestCase
{
	private UnsentMailsSender $unsentMailsSender;

	private MailEntityRepository $repository;

	private MailEntitySaver $saver;

	/**
	 * @throws Throwable
	 */
	public function setUp(): void
	{
		parent::setUp();

		$this->unsentMailsSender = $this->getInstance(UnsentMailsSender::class);
		$this->repository        = $this->getInstance(MailEntityRepository::class);
		$this->saver             = $this->getInstance(MailEntitySaver::class);
	}

	/**
	 * @throws Throwable
	 */
	public function test_unsent_mail_is_claimed_and_sent()
	{
		$mail = $this->createMail();

		$this->assertEquals(1, $this->unsentMailsSender->send());

		$this->assertNotNull($mail->getProcessingAt());
		$this->assertNotNull($mail->getSentAt());
		$this->assertNull($mail->getError());

		// it is not waiting in the queue anymore
		$this->assertEquals(0, $this->unsentMailsSender->send());
	}

	/**
	 * @throws Throwable
	 */
	public function test_mail_claimed_by_another_worker_is_not_sent_again()
	{
		$mail = $this->createMail();

		// another cron or worker got it first
		$this->assertTrue($this->repository->claim($mail));

		$this->assertEquals(0, $this->unsentMailsSender->send());

		$this->assertNull($mail->getSentAt());
		$this->assertNull($mail->getError());
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

		$recipient = new RecipientEntity();
		$recipient->setEmail('recipient@anything.com');
		$recipient->setName('Empfänger');
		$recipient->setType(RecipientEntity::TYPE_TO);
		$recipient->setMail($mail);

		$mail->getRecipients()
			->add($recipient);

		$this->saver->save($mail);

		return $mail;
	}
}
