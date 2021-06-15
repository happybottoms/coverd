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

    /**
     * @param $qb
     * @param ParameterBag $params
     * @return void
     */
    protected function addCriteria(QueryBuilder $qb, ParameterBag $params)
    {
        // Add criteria that applies to all orders (e.g. Status)
        parent::addCriteria($qb, $params);

        if ($params->has('sourceLocation') && $params->get('sourceLocation')) {
            $qb->andWhere('o.sourceLocation = :sourceLocation')
                ->setParameter('sourceLocation', $params->get('sourceLocation'));
        }

        if ($params->has('targetLocation') && $params->get('targetLocation')) {
            $qb->andWhere('o.targetLocation = :targetLocation')
                ->setParameter('targetLocation', $params->get('targetLocation'));
        }
    }
}
