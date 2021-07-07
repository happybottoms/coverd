<?php

namespace App\Repository;

class DefinitionRepository extends BaseRepository
{
    public function findAllSorted()
    {
        return $this->findBy([], ['orderIndex' => 'ASC']);
    }
}
