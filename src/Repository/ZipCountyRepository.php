<?php

namespace App\Repository;

use App\Entity\Setting;

class ZipCountyRepository extends BaseRepository
{
    public function findAllInConstraints(): array
    {
        $zipCountySetting = $this->getEntityManager()->getRepository(Setting::class)->find('zipCountyStates');
        $states = $zipCountySetting->getValue();

        if (empty($states)) {
            throw new \Exception('No states selected for Zip/County field in Admin');
        }

        $qb = $this->createQueryBuilder('z')
            ->where('z.stateCode in (:states)')
            ->setParameter('states', $states)
            ->orderBy('z.zipCode', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function findByZipAndCounty(string $zip, string $county = null): array
    {
        $qb = $this->createQueryBuilder('z')
            ->andWhere('z.zipCode = :zip')
            ->setParameter('zip', $zip);
        if ($county) {
            $qb->andWhere('z.countyName like :county')
                ->setParameter('county', '%' . $county . '%');
        }

        return $qb->getQuery()->getResult();
    }
}
