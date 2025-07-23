<?php
declare(strict_types=1);

namespace Mail\Queue;

use Common\Db\FilterChain;
use Common\Db\OrderChain;
use Mail\Db\MailEntity\Filter\Error;
use Mail\Db\MailEntity\Filter\Sent;
use Mail\Db\MailEntity\Order\CreatedAt;
use Mail\Mail\Sender as MailSender;
use Mail\Db\MailEntityRepository;
use Throwable;

class UnsentMailsSender
{
	private MailEntityRepository $repository;

	private MailSender $mailSender;

	public function __construct(
		MailEntityRepository $repository,
		MailSender $mailSender
	)
	{
		$this->repository = $repository;
		$this->mailSender = $mailSender;
	}

	/**
	 * @throws Throwable
	 */
	public function send(): void
	{
		$mails = $this->repository->filter(
			FilterChain::create()
				->addFilter(Sent::no())
				->addFilter(Error::isNull()),
			OrderChain::create()
				->addOrder(CreatedAt::desc())
		);

		foreach ($mails as $mail)
		{
			$this->mailSender->send($mail);
		}
	}
}
