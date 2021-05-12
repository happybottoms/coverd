<?php

namespace App\Repository\Orders;

use App\Entity\Orders\BulkDistribution;
use App\Entity\Partner;
use App\Repository\OrderRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\ParameterBag;

class BulkDistributionOrderRepository extends OrderRepository
{
    protected function joinRelatedTables(QueryBuilder $qb)
    {
        $qb->leftJoin('o.partner', 'partner');
    }

    public function distributionTotals($sortField = null, $sortDirection = 'ASC', ParameterBag $params = null)
    {
        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.lineItems', 'l')
            ->join('o.partner', 'p');

        if ($sortField && $sortField != 'total') {
            if (strstr($sortField, '.') === false) {
                $sortField = 'o.' . $sortField;
            }
            $qb->orderBy($sortField, $sortDirection);
        }

        $this->addCriteria($qb, $params);

        $results = $qb->getQuery()->execute();
        return $results;
    }

    public function findDistributionTotalsCount(ParameterBag $params)
    {

        $qb = $this->createQueryBuilder('o')
            ->select('p.id')
            ->join('o.partner', 'p')
            ->groupBy('p.id');

        $this->addCriteria($qb, $params);

        $paginator = new Paginator($qb->getQuery());
        return $paginator->count();
    }

    /**
     * @param Partner $partner
     * @return BulkDistribution[]
     */
    public function getDistributionsForForcasting(Partner $partner)
    {
        $monthsBack = $partner->getForecastAverageMonths() ?: 3;

        $qb = $this->createQueryBuilder('o')
            ->andWhere('o.partner = :partner')
            ->setParameter('partner', $partner)
            ->orderBy('o.distributionPeriod', 'DESC')
            ->setMaxResults($monthsBack);

        $results = $qb->getQuery()->execute();
        return $results;
    }

    protected function addCriteria(QueryBuilder $qb, ParameterBag $params)
    {
        parent::addCriteria($qb, $params);

        if ($params->has('partner')) {
            $qb->andWhere('o.partner = :partner')
                ->setParameter('partner', $params->get('partner'));
        }

        if ($params->has('product')) {
            $qb->andWhere('l.product = :product')
                ->setParameter('product', $params->get('product'));
        }

        if ($params->has('partnerType')) {
            $qb->andWhere('p.partnerType = :partnerType')
                ->setParameter('partnerType', $params->get('partnerType'));
        }

        if ($params->has('distributionPeriod')) {
            $qb->andWhere('o.distributionPeriod = :distributionPeriod')
                ->setParameter('distributionPeriod', $params->get('distributionPeriod'));
        }

        if ($params->has('startingAt')) {
            $qb->andWhere('o.distributionPeriod >= :startingAt')
                ->setParameter('startingAt', new \DateTime($params->get('startingAt')));
        }

        if ($params->has('endingAt')) {
            $qb->andWhere('o.distributionPeriod <= :endingAt')
                ->setParameter('endingAt', new \DateTime($params->get('endingAt')));
        }
    }
}
