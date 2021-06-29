<?php

namespace App\Repository\Orders;

use App\Repository\OrderRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\ParameterBag;

class AdjustmentOrderRepository extends OrderRepository
{
    protected function joinRelatedTables(QueryBuilder $qb)
    {
        $qb->leftJoin('o.storageLocation', 'storageLocation');
    } 

    /**
    * @return void
    */
   protected function addCriteria(QueryBuilder $qb, ParameterBag $params)
   {
       parent::addCriteria($qb, $params);

       if ($params->has('storageLocation') && $params->get('storageLocation')) {
           $qb->andWhere('o.storageLocation = :storageLocation')
               ->setParameter('storageLocation', $params->get('storageLocation'));
       }
   }
}
