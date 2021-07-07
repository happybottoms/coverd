<?php

namespace App\Repository;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\ParameterBag;

class SupplierRepository extends BaseRepository
{
    public function findAllPaged(
        $page = null,
        $limit = null,
        $sortField = null,
        $sortDirection = 'ASC',
        ParameterBag $params = null
    ) {
        $qb = $this->createQueryBuilder('s');

        $this->joinRelatedTables($qb);

        if ($page && $limit) {
            $qb->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);
        }

        if ($sortField) {
            if (!strstr($sortField, '.')) {
                $sortField = 's.' . $sortField;
            }
            $qb->orderBy($sortField, $sortDirection);
        }

        $this->addCriteria($qb, $params);

        $results = $qb->getQuery()->execute();
        return $results;
    }

    public function findAllCount(ParameterBag $params)
    {
        $qb = $this->createQueryBuilder('s')
            ->select('count(s)');

        $this->addCriteria($qb, $params);

        return $qb->getQuery()->getSingleScalarResult();
    }

    protected function addCriteria(QueryBuilder $qb, ParameterBag $params)
    {
        if ($params->has('status') && $params->get('status')) {
            $qb->andWhere('s.status = :status')
                ->setParameter('status', $params->get('status'));
        }

        if ($params->has('supplierType') && $params->get('supplierType')) {
            $qb->andWhere('s.supplierType = :supplierType')
                ->setParameter('supplierType', $params->get('supplierType'));
        }

        if ($params->has('keyword') && $params->get('keyword')) {
            $qb->andWhere('s.title LIKE :keyword')
                ->setParameter('keyword', '%' . $params->get('keyword') . '%');
        }
    }

    protected function joinRelatedTables(QueryBuilder $qb)
    {
    }
}
