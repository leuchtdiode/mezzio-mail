<?php
declare(strict_types=1);

namespace Mail\Health;

use Common\Db\FilterChain;
use DateTime;
use Mail\Db\MailEntity\Filter\CreatedAt;
use Mail\Db\MailEntity\Filter\Error;
use Mail\Db\MailEntity\Filter\Sent;
use Mail\Db\MailEntityRepository;
use Monitoring\Health\Check;
use Monitoring\Health\CheckResult;
use RuntimeException;
use Throwable;

readonly class UnsentMailsCheck implements Check
{
	private const int DEFAULT_THRESHOLD_MINUTES = 60;

	public function __construct(
		private array $config,
		private MailEntityRepository $repository
	)
	{
	}

	/**
	 * @throws Throwable
	 */
	public function check(): CheckResult
	{
		if (!interface_exists('\Monitoring\Health\Check'))
		{
			throw new RuntimeException('leuchtdiode/mezzio-monitoring is mandatory');
		}

		$result = new CheckResult();
		$result->setKey('mail-unsent-mails');

		$thresholdMinutes = $this->getThresholdMinutes();

		$createdBefore = new DateTime();
		$createdBefore->modify('-' . $thresholdMinutes . ' minute');

		// mails with an error are never picked up again, so only the ones still waiting in the queue count
		$unsentCount = $this->repository->countWithFilter(
			FilterChain::create()
				->addFilter(Sent::no())
				->addFilter(Error::isNull())
				->addFilter(CreatedAt::before($createdBefore))
		);

		$result->setHealthy($unsentCount === 0);

		if ($unsentCount)
		{
			$result->addMessage(sprintf(
				'%d mail(s) are unsent for longer than %d minute(s), please check',
				$unsentCount,
				$thresholdMinutes
			));
		}

		return $result;
	}

	private function getThresholdMinutes(): int
	{
		return (int)($this->config['mail']['monitoring']['unsentMails']['thresholdMinutes']
			?? self::DEFAULT_THRESHOLD_MINUTES);
	}
}
