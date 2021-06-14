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

    protected function addCriteria(QueryBuilder $qb, ParameterBag $params)
    {
        if ($params->has('status') && $params->get('status')) {
            $qb->andWhere('o.status = :status')
                ->setParameter('status', $params->get('status'));
        }

        if ($params->has('sourceLocation') && $params->get('sourceLocation')) {
            dump($qb->getQuery());
            $qb->andWhere('o.sourceLocation = :sourceLocation')
                ->setParameter('sourceLocation', $params->get('sourceLocation'));
        }
        
        if ($params->has('targetLocation') && $params->get('status')) {
            $qb->andWhere('o.targetLocation = :targetLocation')
                ->setParameter('targetLocation', $params->get('targetLocation'));
        }
    }
}
