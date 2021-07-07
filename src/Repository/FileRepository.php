<?php

namespace App\Repository;

class FileRepository extends BaseRepository
{
    public function findByAnyIdentifier($id): ?object
    {
        $qb = $this->createQueryBuilder('f');
        if (is_string($id)) {
            $qb->andWhere("f.publicId = :id");
        } else {
            $qb->andWhere("f.id = :id");
        }
        $qb->setParameter('id', $id);
        $results = $qb->getQuery()->execute();
        return reset($results);
    }
}
