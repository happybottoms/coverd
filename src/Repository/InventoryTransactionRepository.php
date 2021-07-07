<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\StorageLocation;
use Doctrine\ORM\QueryBuilder;
use Moment\Moment;
use Symfony\Component\HttpFoundation\ParameterBag;

class InventoryTransactionRepository extends BaseRepository
{
//    protected $_entityName = 'App\Entity\InventoryTransaction';

    public function getStockLevels($includeAllocated = false, ParameterBag $params = null)
    {
        $qb = $this->createQueryBuilder('t')
            ->select('p.id, p.name, c.name as category, SUM(t.delta) as balance')
            ->join('t.product', 'p')
            ->join('p.productCategory', 'c')
            ->groupBy('c.name', 'p.id');

        if (!$includeAllocated) {
            $qb->andWhere('t.committed = :committed')
                ->setParameter('committed', true);
        }

        if ($params) {
            if ($params->has('location')) {
                $qb->andWhere('t.storageLocation = :location');
                $qb->setParameter('location', $params->get('location'));
            }

            if ($params->has('endingAt')) {
                if (!$includeAllocated) {
                    $qb->andWhere('t.committedAt < :endingAt');
                } else {
                    $qb->andWhere('t.createdAt < :endingAt');
                }
                $moment = new Moment($params->get('endingAt'), 'America/Chicago');
                $moment->endOf('day');

                $qb->setParameter('endingAt', $moment->setTimezone('UTC'));
            }

            if ($params->has('locationType')) {
                $qb->join('t.storageLocation', 's');

                $qb->andWhere('s INSTANCE OF :locationType');
                if ($params->get('locationType') == StorageLocation::TYPE_WAREHOUSE) {
                    $qb->setParameter('locationType', 'warehouse');
                } elseif ($params->get('locationType') == StorageLocation::TYPE_PARTNER) {
                    $qb->setParameter('locationType', 'partner');
                }
            }
        }

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * @param \DateTime $startAt
     * @param \DateTime $endAt
     * @return array
     */
    public function getDailyStockLevels(?\DateTime $startAt = null, ?\DateTime $endAt = null)
    {
        if (!$startAt) {
            $monthAgo = new Moment();
            $startAt = $monthAgo->subtractDays(30)->startOf('day');
        }

        if (!$endAt) {
            $today = new Moment();
            $endAt = $today->endOf('day');
        }

        $qb = $this->createQueryBuilder('t')
            ->select('t.delta, t.createdAt')
            ->andWhere('t.createdAt >= :startAt')
            ->andWhere('t.createdAt <= :endAt')
            ->setParameters(["startAt" => $startAt, "endAt" => $endAt]);

        return $qb->getQuery()->getArrayResult();
    }

    public function getStorageLocationInventory(StorageLocation $location)
    {
        $dql = "SELECT p.id, SUM(t.delta) AS balance
          FROM App\Entity\InventoryTransaction t
          JOIN t.product p
          WHERE t.committed = 1
              and t.storageLocation = :location
          GROUP BY p.id";

        $inventory = $this->getEntityManager()->createQuery($dql)
            ->setParameter('location', $location)
            ->getArrayResult();

        return $inventory;
    }

    public function getNetworkInventory(Product $product)
    {
        $dql = "SELECT SUM(t.delta) AS balance FROM App\Entity\InventoryTransaction t
            WHERE t.product = ?1
              and t.committed = 1";

        $inventory = $this->getEntityManager()->createQuery($dql)
            ->setParameter(1, $product)
            ->getSingleScalarResult();

        return $inventory;
    }

    public function getPartnerInventory(Product $product)
    {
        $dql = "SELECT SUM(t.delta) AS balance FROM App\Entity\InventoryTransaction t
            WHERE t.product = ?1
              and t.committed = 1
              and t.storageLocation in (SELECT w FROM App\Entity\Partner as w)";

        $inventory = $this->getEntityManager()->createQuery($dql)
            ->setParameter(1, $product)
            ->getSingleScalarResult();

        return $inventory;
    }

    public function getReportTransactions($includeAllocated = false, ParameterBag $params = null)
    {
        $qb = $this->createQueryBuilder('t');

        $this->addCriteria($qb, $params);

        $transactions = $qb->getQuery()->execute();

        return $transactions;
    }

    public function findByPartnerOrderable($partnerOrderable)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('p')
            ->from('App\Entity\Product', 'p')
            ->join('p.productCategory', 'pc')
            ->where('pc.isPartnerOrderable = :isPartnerOrderable')
            ->setParameter('isPartnerOrderable', (bool) $partnerOrderable);

        $results = $qb->getQuery()->execute();

        return $results;
    }

    public function findAllPaged(
        $page = null,
        $limit = null,
        $sortField = null,
        $sortDirection = 'ASC',
        ParameterBag $params = null
    ) {
        $qb = $this->createQueryBuilder('t')
            ->join('t.product', 'p')
            ->join('t.lineItem', 'l')
            ->join('l.order', 'o')
            ->join('t.storageLocation', 's');

        if ($page && $limit) {
            $qb->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);
        }

        if ($sortField) {
            if (strstr($sortField, '.') === false) {
                $sortField = 't.' . $sortField;
            }
            $qb->orderBy($sortField, $sortDirection);
        }

        $this->addCriteria($qb, $params);

        $results = $qb->getQuery()->execute();
        return $results;
    }

    public function findAllCount(ParameterBag $params)
    {
        $qb = $this->createQueryBuilder('t')
            ->join('t.product', 'p')
            ->join('t.lineItem', 'l')
            ->join('l.order', 'o')
            ->join('t.storageLocation', 's')
            ->select('count(t)');

        $this->addCriteria($qb, $params);

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $qb
     * @param ParameterBag $params
     */
    public function addCriteria(QueryBuilder $qb, ParameterBag $params): void
    {
        if ($params->has('location')) {
            $qb->andWhere('t.storageLocation = :location')
                ->setParameter('location', $params->get('location'));
        }

        if ($params->has('product')) {
            $qb->andWhere('t.product = :product')
                ->setParameter('product', $params->get('product'));
        }

        if ($params->has('orderType')) {
            $qb->andWhere(
                $qb->expr()
                    ->isInstanceOf(
                        'o',
                        "App\\Domain\\Entities\\Orders\\" . $params->get('orderType')
                    )
            );
        }

        if ($params->has('startingAt')) {
            $qb->andWhere('t.createdAt >= :startingAt')
                ->setParameter('startingAt', new \DateTime($params->get('startingAt')));
        }

        if ($params->has('endingAt')) {
            $qb->andWhere('t.createdAt <= :endingAt')
                ->setParameter('endingAt', new \DateTime($params->get('endingAt')));
        }
    }
}
