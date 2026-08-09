<?php
declare(strict_types=1);

namespace Mail\Db\MailEntity\Filter;

use Common\Db\Filter\Date;

class CreatedAt extends Date
{
	protected function getColumn(): string
	{
		return 't.createdAt';
	}
}
