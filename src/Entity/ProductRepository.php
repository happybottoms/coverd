<?php

namespace App\Entity;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\ParameterBag;
use Doctrine\ORM\QueryBuilder;

class ProductRepository extends EntityRepository
{
    public function findAllSorted()
    {
        return $this->findBy([], ['orderIndex' => 'ASC']);
    }

    public function getWarehouseInventory(Product $product)
    {
        $dql = "SELECT SUM(t.delta) AS balance FROM App\Entity\InventoryTransaction t
            WHERE t.product = ?1
              and t.committed = ?2
              and t.storageLocation in (SELECT w FROM App\Entity\Warehouse as w)";

        $inventory = $this->getEntityManager()->createQuery($dql)
            ->setParameter(1, $product)
            ->setParameter(2, true)
            ->getSingleScalarResult();

        return $inventory;
    }

    public function getNetworkInventory(Product $product)
    {
        $dql = "SELECT SUM(t.delta) AS balance FROM App\Entity\InventoryTransaction t
            WHERE t.product = ?1
              and t.committed = ?2";

        $inventory = $this->getEntityManager()->createQuery($dql)
            ->setParameter(1, $product)
            ->setParameter(2, true)
            ->getSingleScalarResult();

        return $inventory;
    }

    public function getPartnerInventory(Product $product)
    {
        $dql = "SELECT SUM(t.delta) AS balance FROM App\Entity\InventoryTransaction t
            WHERE t.product = ?1
              and t.committed = ?2
              and t.storageLocation in (SELECT w FROM App\Entity\Partner as w)";

        $inventory = $this->getEntityManager()->createQuery($dql)
            ->setParameter(1, $product)
            ->setParameter(2, true)
            ->getSingleScalarResult();

        return $inventory;
    }

    public function findByPartnerOrderable($partnerOrderable = true)
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
        $this->joinRelatedTables($qb);

        $this->addCriteria($qb, $params);

        return $qb->getQuery()->getSingleScalarResult();
    }

    protected function joinRelatedTables(QueryBuilder $qb)
    {
        $qb->join('s.productCategory', 'pc');
    }

    protected function addCriteria(QueryBuilder $qb, ParameterBag $params)
    {
        if ($params->has('status') && $params->get('status')) {
            $qb->andWhere('s.status = :status')
                ->setParameter('status', $params->get('status'));
        }

        if ($params->has('category') && $params->get('category')) {
            $qb->andWhere('pc.id = :categoryId')
                ->setParameter('categoryId', $params->get('category'));
        }

        if ($params->has('name') && $params->get('name')) {
            $qb->andWhere('s.name LIKE :name')
                ->setParameter('name', '%' . $params->get('name') . '%');
        }
    }
}
