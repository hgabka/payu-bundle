<?php

namespace Hgabka\PayUBundle\Repository;

use Doctrine\ORM\EntityRepository;

class PayUTransactionRepository extends EntityRepository
{
    /**
     * @param $orderId
     */
    public function getTransactionByOrderId($orderId)
    {
        return
            $this
                ->createQueryBuilder('t')
                ->where('t.shopOrderId = :orderid')
                ->setParameter('orderid', $orderId)
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult()
        ;
    }
}
