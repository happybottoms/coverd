<?php

namespace App\Repository\Orders;

use App\Entity\Client;
use App\Entity\Orders\BulkDistribution;
use App\Repository\BaseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr\Join;

class BulkDistributionLineItemRepository extends BaseRepository
{
    /**
     * @param Client $client
     * @return array|ArrayCollection
     */
    public function getClientDistributionHistory(Client $client)
    {
        $qb = $this->createQueryBuilder('l')
            ->innerJoin(BulkDistribution::class, 'o', Join::WITH, 'l.order = o.id')
            ->andWhere('l.client = :client')
            ->setParameter('client', $client)
            ->orderBy('o.distributionPeriod', 'ASC');

        return $qb->getQuery()->execute();
    }
}
