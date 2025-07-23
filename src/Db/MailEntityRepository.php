<?php
declare(strict_types=1);

namespace Mail\Db;

use Common\Db\EntityRepository;
use Common\Db\FilterChain;
use Common\Db\OrderChain;

/**
 * @method MailEntity[] filter(FilterChain $filterChain, OrderChain $orderChain = null, int $offset = 0, int $limit = PHP_INT_MAX, bool $distinct = false)
 */
class MailEntityRepository extends EntityRepository
{

}