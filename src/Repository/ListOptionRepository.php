<?php

namespace App\Repository;

class ListOptionRepository extends BaseRepository
{
    public function findOneByName($name)
    {
        return $this->findOneBy(['name' => $name]);
    }
}
