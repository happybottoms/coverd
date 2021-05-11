<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\ParameterBag;

class ProductRepository extends EntityRepository
{
    public function findAllPaged(
        ?int $page = null,
        ?int $limit = null,
        ?string $sortField = null,
        ?string $sortDirection = 'ASC',
        ?ParameterBag $params = null
    ): ArrayCollection {
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
        } else {
            $qb->orderBy('p.orderIndex', 'ASC');
        }

        $this->addCriteria($qb, $params);

        return $qb->getQuery()->execute();
    }

    public function findAllCount(ParameterBag $params): int
    {
        $qb = $this->createQueryBuilder('p')
            ->select('count(p)');

        $this->addCriteria($qb, $params);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function findAllSorted(): array
    {
        return $this->findBy([], ['orderIndex' => 'ASC']);
    }

    public function getWarehouseInventory(Product $product): int
    {
        $dql = "SELECT SUM(t.delta) AS balance FROM App\Entity\InventoryTransaction t
            WHERE t.product = ?1
              and t.committed = ?2
              and t.storageLocation in (SELECT w FROM App\Entity\Warehouse as w)";

        $inventory = $this->getEntityManager()->createQuery($dql)
            ->setParameter(1, $product)
            ->setParameter(2, true)
            ->getSingleScalarResult();

        return (int) $inventory;
    }

    public function getNetworkInventory(Product $product): int
    {
        $dql = "SELECT SUM(t.delta) AS balance FROM App\Entity\InventoryTransaction t
            WHERE t.product = ?1
              and t.committed = ?2";

        $inventory = $this->getEntityManager()->createQuery($dql)
            ->setParameter(1, $product)
            ->setParameter(2, true)
            ->getSingleScalarResult();

        return (int) $inventory;
    }

    public function getPartnerInventory(Product $product): int
    {
        $dql = "SELECT SUM(t.delta) AS balance FROM App\Entity\InventoryTransaction t
            WHERE t.product = ?1
              and t.committed = ?2
              and t.storageLocation in (SELECT w FROM App\Entity\Partner as w)";

        $inventory = $this->getEntityManager()->createQuery($dql)
            ->setParameter(1, $product)
            ->setParameter(2, true)
            ->getSingleScalarResult();

        return (int) $inventory;
    }

    /**
     * @param bool $partnerOrderable
     * @return Product[]|ArrayCollection
     */
    public function findByPartnerOrderable(bool $partnerOrderable = true)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('p')
            ->from('App\Entity\Product', 'p')
            ->join('p.productCategory', 'pc')
            ->where('pc.isPartnerOrderable = :isPartnerOrderable')
            ->setParameter('isPartnerOrderable', (bool) $partnerOrderable);

        return $qb->getQuery()->execute();
    }


    protected function addCriteria(QueryBuilder $qb, ?ParameterBag $params): void
    {
        if (!$params) {
            return;
        }

        if ($params->has('partnerOrderable') && $params->get('partnerOrderable')) {
            $qb->andWhere('pc.isPartnerOrderable = :isPartnerOrderable')
                ->setParameter('isPartnerOrderable', (bool) $params->get('partnerOrderable', true));
        }
    }

    protected function joinRelatedTables(QueryBuilder $qb): void
    {
        $qb->join('p.productCategory', 'pc');
    }
}
