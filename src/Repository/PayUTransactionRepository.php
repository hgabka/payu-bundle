<?php

namespace Hgabka\PayUBundle\Repository;

use Doctrine\ORM\EntityRepository;

class PayUTransactionRepository extends EntityRepository
{
    public function createOrGetTransaction($params, $statusArray, $check = false)
    {
        $orderId = $params['order_ref'];

        if (!$orderId)
        {
            throw new hgPayUException('Ures order id');
        }

        $transaction = $this->createQuery('t')
                            ->where('t.ShopOrderId = ?', $orderId)
                            ->fetchOne();

        if (!$transaction)
        {
            $transaction = new hgPayuTransaction();
        }

        if ($transaction->isNew() || $transaction->getState() != hgPayUPayment::STATUS_SUCCESS)
        {
            $transaction->setShopOrderId($orderId);
            if (isset($params['RC']))
            {
                $transaction->setPayuRc($params['RC']);
            }

            if (isset($params['RT']))
            {
                $transaction->setPayuRt($params['RT']);
            }

            if (isset($params['date']))
            {
                $transaction->setPayuDate($params['date']);
            }

            if (!$statusArray)
            {
                $transaction->setState(hgPayUPayment::STATUS_CANCEL);
            }
            else
            {
                $transaction->setState($check ? hgPayUPayment::STATUS_PENDING : hgPayUPayment::STATUS_CANCEL);
                $transaction->setPayuRefno($statusArray['PAYREFNO']);
                $transaction->setPaymentType($statusArray['PAYMETHOD']);
                $transaction->setPayuStatus($statusArray['ORDER_STATUS']);
            }
        }

        $transaction->save();

        return $transaction;

    }
}