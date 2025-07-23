<?php
declare(strict_types=1);

namespace Mail\Command\Queue;

use Mail\Command\BaseCommand;
use Mail\Queue\UnsentMailsSender;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class Send extends BaseCommand
{
	private UnsentMailsSender $unsentMailsSender;

	public function __construct(UnsentMailsSender $unsentMailsSender)
	{
		$this->unsentMailsSender = $unsentMailsSender;

		parent::__construct();
	}

	protected function configure(): void
	{
		$this
			->setName('mail:queue:send')
			->setDescription('Sends unsent mails in queue');
	}

	/**
	 * @throws Throwable
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$this->unsentMailsSender->send();

		return self::SUCCESS;
	}
}
