<?php
declare(strict_types=1);

namespace Mail\Queue;

use Common\Db\FilterChain;
use Common\Db\OrderChain;
use Common\Shutdown\State as ShutdownState;
use Mail\Db\MailEntity;
use Mail\Db\MailEntity\Filter\Error;
use Mail\Db\MailEntity\Filter\Sent;
use Mail\Db\MailEntity\Order\CreatedAt;
use Mail\Db\MailEntityRepository;
use Mail\Mail\Sender as MailSender;
use Throwable;

class UnsentMailsSender
{
	public function __construct(
		private readonly MailEntityRepository $repository,
		private readonly MailSender $mailSender,
		private readonly ShutdownState $shutdownState
	)
	{
	}

	/**
	 * Returns the number of mails that have been handled by this run.
	 *
	 * @throws Throwable
	 */
	public function send(): int
	{
		if ($this->shutdownState->isShuttingDown())
		{
			return 0;
		}

		$mails = $this->repository->filter(
			FilterChain::create()
				->addFilter(Sent::no())
				->addFilter(Error::isNull()),
			OrderChain::create()
				->addOrder(CreatedAt::desc())
		);

		$handledCount = 0;

		foreach ($mails as $mail)
		{
			// check before every single mail, so a shutdown request only has to wait for the running one
			if ($this->shutdownState->isShuttingDown())
			{
				break;
			}

			if ($this->sendMail($mail))
			{
				$handledCount++;
			}
		}

		return $handledCount;
	}

	/**
	 * Returns false if the mail is still unsent afterwards and will be picked up again,
	 * e.g. because not even the error could be stored.
	 */
	private function sendMail(MailEntity $mail): bool
	{
		try
		{
			// the sender stores the error itself, so a failed mail is not picked up again
			$this->mailSender->send($mail);
		}
		catch (Throwable $e)
		{
			// a single mail must not stop the whole run, so only log it and keep going
			error_log(sprintf(
				'Mail %s could not be sent: %s - %s',
				$mail->getId()
					->toString(),
				get_class($e),
				$e->getMessage()
			));

			return false;
		}

		return true;
	}
}
