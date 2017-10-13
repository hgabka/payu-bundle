<?php

namespace Hgabka\PayUBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Hgabka\PayUBundle\Entity\PayUTransaction;

class PayUTransactionRepository extends EntityRepository
{
    /**
     * @param $orderId
     * @return null | PayUTransaction
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
