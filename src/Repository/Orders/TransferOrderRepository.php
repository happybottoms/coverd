<?php

namespace App\Repository\Orders;

use App\Repository\OrderRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\ParameterBag;

class TransferOrderRepository extends OrderRepository
{
    protected function joinRelatedTables(QueryBuilder $qb)
    {
        $qb->leftJoin('o.sourceLocation', 'sourceLocation')
            ->leftJoin('o.targetLocation', 'targetLocation');
    }
}
