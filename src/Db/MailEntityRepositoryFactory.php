<?php
declare(strict_types=1);

namespace Mail\Db;

use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class MailEntityRepositoryFactory implements FactoryInterface
{
	public function __invoke(
		ContainerInterface $container,
		$requestedName,
		array $options = null
	): object
	{
		return $container
			->get(EntityManager::class)
			->getRepository(MailEntity::class);
	}
}
