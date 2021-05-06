<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\ParameterBag;

class PartnerRepository extends EntityRepository
{
    public function findAllPaged(
        $page = null,
        $limit = null,
        $sortField = null,
        $sortDirection = 'ASC',
        ParameterBag $params = null
    ) {
        $qb = $this->createQueryBuilder('p');

        $this->joinRelatedTables($qb);

        if ($page && $limit) {
            $qb->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);
        }

        if ($sortField) {
            if (!strstr($sortField, '.')) {
                $sortField = 'p.' . $sortField;
            }
            $qb->orderBy($sortField, $sortDirection);
        }

        $this->addCriteria($qb, $params);

        return $qb->getQuery()->execute();
    }

    public function findAllCount(ParameterBag $params)
    {
        $qb = $this->createQueryBuilder('p')
            ->select('count(p)');

        $this->addCriteria($qb, $params);

        return $qb->getQuery()->getSingleScalarResult();
    }

    protected function addCriteria(QueryBuilder $qb, ParameterBag $params): void
    {
        if ($params->has('status') && $params->get('status')) {
            $qb->andWhere('p.status = :status')
                ->setParameter('status', $params->get('status'));
        }

        if ($params->has('name') && $params->get('name')) {
            $qb->andWhere('p.name = :name')
                ->setParameter('name', $params->get('name'));
        }

        if ($params->has('type') && $params->get('type')) {
            $qb->andWhere('p.type = :type')
                ->setParameter('type', $params->get('type'));
        }

        if ($params->has('fulfillmentPeriod') && $params->get('fulfillmentPeriod')) {
            $qb->andWhere('p.fulfillmentPeriod = :fulfillmentPeriod')
                ->setParameter('fulfillmentPeriod', $params->get('fulfillmentPeriod'));
        }

        if ($params->has('distributionMethod') && $params->get('distributionMethod')) {
            $qb->andWhere('p.distributionMethod = :distributionMethod')
                ->setParameter('distributionMethod', $params->get('distributionMethod'));
        }
    }
    
    protected function joinRelatedTables(QueryBuilder $qb)
    {
    }
}
