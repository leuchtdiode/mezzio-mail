<?php
declare(strict_types=1);

namespace Mail\Command\Queue;

use Mail\Command\BaseCommand;
use Mail\Queue\UnsentMailsSender;
use Mail\Queue\Worker;
use Mail\Queue\WorkerParams;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class Send extends BaseCommand
{
	private const string WORKER      = 'worker';
	private const string SLEEP       = 'sleep';
	private const string MAX_RUNTIME = 'max-runtime';

	private const float DEFAULT_SLEEP_SECONDS = 1.0;

	public function __construct(
		private readonly UnsentMailsSender $unsentMailsSender,
		private readonly Worker $worker
	)
	{
		parent::__construct();
	}

	protected function configure(): void
	{
		$this
			->setName('mail:queue:send')
			->setDescription('Sends unsent mails in queue')
			->addOption(
				self::WORKER,
				null,
				InputOption::VALUE_NONE,
				'Keep running and send mails as soon as they show up, until a shutdown is requested'
			)
			->addOption(
				self::SLEEP,
				null,
				InputOption::VALUE_REQUIRED,
				'Seconds to wait before looking for mails again when there was nothing to do, only used with --' . self::WORKER,
				self::DEFAULT_SLEEP_SECONDS
			)
			->addOption(
				self::MAX_RUNTIME,
				null,
				InputOption::VALUE_REQUIRED,
				'Stop after the given seconds, only used with --' . self::WORKER
			);
	}

	/**
	 * @throws Throwable
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		if (!$input->getOption(self::WORKER))
		{
			$this->unsentMailsSender->send();

			return self::SUCCESS;
		}

		$maxRuntime = $input->getOption(self::MAX_RUNTIME);

		$this->worker->run(
			WorkerParams::create()
				->setSleepSeconds(
					(float)($input->getOption(self::SLEEP) ?? self::DEFAULT_SLEEP_SECONDS)
				)
				->setMaxRuntimeSeconds(
					$maxRuntime !== null
						? (int)$maxRuntime
						: null
				)
		);

		return self::SUCCESS;
	}
}
