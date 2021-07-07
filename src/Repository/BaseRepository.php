<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class BaseRepository extends EntityRepository
{
    /**
     * @param mixed $id
     * @return object|null
     */
    public function findByAnyIdentifier($id): ?object
    {
        return $this->find($id);
    }
}
