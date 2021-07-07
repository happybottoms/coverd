<?php

namespace App\Repository;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\ParameterBag;

class OrderRepository extends BaseRepository
{
    public function findAllPaged(
        $page = null,
        $limit = null,
        $sortField = null,
        $sortDirection = 'ASC',
        ParameterBag $params = null
    ) {
        $qb = $this->createQueryBuilder('o');

        $this->joinRelatedTables($qb);

        if ($page && $limit) {
            $qb->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);
        }

        if ($sortField) {
            if (!strstr($sortField, '.')) {
                $sortField = 'o.' . $sortField;
            }
            $qb->orderBy($sortField, $sortDirection);
        }

        $this->addCriteria($qb, $params);

        return $qb->getQuery()->execute();
    }

    public function findAllCount(ParameterBag $params)
    {
        $qb = $this->createQueryBuilder('o')
            ->select('count(o)');

        $this->addCriteria($qb, $params);

        return $qb->getQuery()->getSingleScalarResult();
    }

    protected function addCriteria(QueryBuilder $qb, ParameterBag $params)
    {
        if ($params->has('status') && $params->get('status')) {
            $qb->andWhere('o.status = :status')
                ->setParameter('status', $params->get('status'));
        }

        if ($params->has('fulfillmentPeriod') && $params->get('fulfillmentPeriod')) {
            $qb->join('o.partner', 'p');
            $qb->join('p.fulfillmentPeriod', 'f');
            $qb->andWhere('f.id = :fulfillmentPeriod')
                ->setParameter('fulfillmentPeriod', $params->get('fulfillmentPeriod'));
        }
    }

    protected function joinRelatedTables(QueryBuilder $qb)
    {
    }
}
