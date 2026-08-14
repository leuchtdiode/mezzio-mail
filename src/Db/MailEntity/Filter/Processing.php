<?php
declare(strict_types=1);

namespace Mail\Db\MailEntity\Filter;

use Common\Db\Filter\Equals;

class Processing extends Equals
{
	protected function getField(): string
	{
		return 't.processingAt';
	}
}
