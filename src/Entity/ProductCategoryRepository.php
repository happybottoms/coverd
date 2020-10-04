<?php

namespace App\Entity;

use Doctrine\ORM\EntityRepository;

class ProductCategoryRepository extends EntityRepository
{
    public function findAll()
    {
        return $this->createQueryBuilder('pc')
            ->andWhere('pc.deletedAt IS NULL')
            ->getQuery()
            ->execute();
    }
}
