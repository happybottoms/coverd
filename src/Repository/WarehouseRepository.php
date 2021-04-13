<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\ParameterBag;

class WarehouseRepository extends EntityRepository
{
    public function findAllPaged(
        ?int $page = null,
        ?int $limit = null,
        ?string $sortField = null,
        ?string $sortDirection = 'ASC',
        ParameterBag $params = null
    ): array {
        $qb = $this->createQueryBuilder('w');

        if ($page && $limit) {
            $qb->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);
        }

        if ($sortField) {
            if (!strstr($sortField, '.')) {
                $sortField = 'w.' . $sortField;
            }
            $qb->orderBy($sortField, $sortDirection);
        }

        if ($params) {
            $this->addCriteria($qb, $params);
        }

        return $qb->getQuery()->execute();
    }

    protected function addCriteria(QueryBuilder $qb, ParameterBag $params): void
    {
        if ($params->has('status') && $params->get('status')) {
            $qb->andWhere('w.status = :status')
                ->setParameter('status', $params->get('status'));
        }

        if ($params->has('id') && $params->get('id')) {
            $qb->andWhere('w.id = :id')
                ->setParameter('id', $params->get('id'));
        }
    }
}
