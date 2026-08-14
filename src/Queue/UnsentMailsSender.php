<?php
declare(strict_types=1);

namespace Mail\Queue;

use Common\Db\FilterChain;
use Common\Db\OrderChain;
use Common\Shutdown\State as ShutdownState;
use Mail\Db\MailEntity\Filter\Error;
use Mail\Db\MailEntity\Filter\Processing;
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
	 * Returns the number of mails that have been claimed and sent by this run.
	 *
	 * @throws Throwable
	 */
	public function send(): int
	{
		if ($this->shutdownState->isShuttingDown())
		{
			return 0;
		}

		// only fetch the ids, every mail is loaded again right before it is claimed anyway
		$ids = $this->repository->filterAndReturnIds(
			FilterChain::create()
				->addFilter(Sent::no())
				->addFilter(Error::isNull())
				->addFilter(Processing::isNull()),
			OrderChain::create()
				->addOrder(CreatedAt::desc())
		);

		$handledCount = 0;

		foreach ($ids as $id)
		{
			// check before every single mail, so a shutdown request only has to wait for the running one
			if ($this->shutdownState->isShuttingDown())
			{
				break;
			}

			if ($this->sendMail($id))
			{
				$handledCount++;
			}
		}

		return $handledCount;
	}

	/**
	 * Returns false if the mail has not been handled by this run,
	 * e.g. because another cron or worker claimed it first.
	 */
	private function sendMail(mixed $id): bool
	{
		// reload, sending a previous mail may have cleared the entity manager
		if (!($mail = $this->repository->find($id)))
		{
			return false;
		}

		// claim atomically, so a mail which several of them fetched is only sent by one of them
		if (!$this->repository->claim($mail))
		{
			return false;
		}

		try
		{
			// the sender stores the error itself, so a failed mail is not picked up again
			$this->mailSender->send($mail);
		}
		catch (Throwable $e)
		{
			// a single mail must not stop the whole run, so only log it and keep going
			// it stays claimed on purpose, it may have been sent already and the health check reports it
			error_log(sprintf(
				'Mail %s could not be sent: %s - %s',
				$mail->getId()
					->toString(),
				get_class($e),
				$e->getMessage()
			));
		}

		return true;
	}
}
