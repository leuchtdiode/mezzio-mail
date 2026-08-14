<?php
declare(strict_types=1);

namespace Mail\Db;

use Common\Db\EntityRepository;
use Common\Db\FilterChain;
use Common\Db\OrderChain;
use DateTime;
use Ramsey\Uuid\Doctrine\UuidType;

/**
 * @method MailEntity[] filter(FilterChain $filterChain, OrderChain $orderChain = null, int $offset = 0, int $limit = PHP_INT_MAX, bool $distinct = false)
 */
class MailEntityRepository extends EntityRepository
{
	/**
	 * Atomically claims the mail for the current cron or worker, so it is the only one which sends it.
	 * Returns false if the mail is not waiting in the queue anymore, e.g. because another one claimed it first.
	 */
	public function claim(MailEntity $mail): bool
	{
		$processingAt = new DateTime();

		$affectedRows = $this
			->getEntityManager()
			->createQuery(
				'UPDATE ' . MailEntity::class . ' m'
				. ' SET m.processingAt = :processingAt'
				. ' WHERE m.id = :id AND m.processingAt IS NULL AND m.sentAt IS NULL AND m.error IS NULL'
			)
			->setParameter('processingAt', $processingAt)
			->setParameter('id', $mail->getId(), UuidType::NAME)
			->execute();

		if ($affectedRows !== 1)
		{
			return false;
		}

		// the update bypasses the entity manager, so keep the loaded entity in sync
		$mail->setProcessingAt($processingAt);

		return true;
	}
}
