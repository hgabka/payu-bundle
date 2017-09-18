<?php

namespace Hgabka\PayUBundle\Event;

use Hgabka\PayUBundle\Entity\PayUTransaction;
use Symfony\Component\EventDispatcher\Event;

class PayUEvent extends Event
{
    const EVENT_TRANSACTION_CREATED = 'hgabka_payu.transaction_created';
    const EVENT_PAYMENT_CONFIRMED = 'hgabka_payu.payment_confirmed';

    /** @var array */
    protected $parameters = [];

    /** @var null|array */
    protected $response;

    /** @var PayUTransaction */
    protected $transaction;

    /** @var int */
    protected $orderId;

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     *
     * @return PayUEvent
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * @return null|array
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param null|array $response
     *
     * @return PayUEvent
     */
    public function setResponse($response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * @return null|PayUTransaction
     */
    public function getTransaction()
    {
        return $this->transaction;
    }

    /**
     * @param null|PayUTransaction $transaction
     *
     * @return PayUEvent
     */
    public function setTransaction($transaction)
    {
        $this->transaction = $transaction;

        return $this;
    }

    /**
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }

    /**
     * @param int $orderId
     *
     * @return PayUEvent
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;

        return $this;
    }
}
