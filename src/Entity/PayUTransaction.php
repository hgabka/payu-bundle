<?php

namespace Hgabka\PayUBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * PayU transaction.
 *
 * @ORM\Entity(repositoryClass="Hgabka\PayUBundle\Repository\PayUTransactionRepository")
 * @ORM\Table(name="hg_payu_transaction")
 */
class PayUTransaction
{
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="payu_refno", nullable=true)
     */
    protected $payuRefno;

    /**
     * @var integer
     *
     * @ORM\Column(type="bigint", name="shop_order_id", nullable=true)
     */
    protected $shopOrderId;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="state", nullable=true)
     */
    protected $state;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="payu_status", nullable=true)
     */
    protected $payuStatus;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="payu_rc", nullable=true)
     */
    protected $payuRc;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="payu_rt", nullable=true)
     */
    protected $payuRt;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="payu_date", nullable=true)
     */
    protected $payuDate;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="payment_type", nullable=true)
     */
    protected $paymentType;

    /**
     * @var float
     *
     * @ORM\Column(type="float", name="amount", nullable=true)
     */
    protected $amount;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="currency", nullable=true)
     */
    protected $currency;


    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", name="created_at")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", name="updated_at")
     */
    protected $updatedAt;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return PayUTransaction
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getPayuRefno()
    {
        return $this->payuRefno;
    }

    /**
     * @param string $payuRefno
     * @return PayUTransaction
     */
    public function setPayuRefno($payuRefno)
    {
        $this->payuRefno = $payuRefno;

        return $this;
    }

    /**
     * @return int
     */
    public function getShopOrderId()
    {
        return $this->shopOrderId;
    }

    /**
     * @param int $shopOrderId
     * @return PayUTransaction
     */
    public function setShopOrderId($shopOrderId)
    {
        $this->shopOrderId = $shopOrderId;

        return $this;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $state
     * @return PayUTransaction
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return string
     */
    public function getPayuStatus()
    {
        return $this->payuStatus;
    }

    /**
     * @param string $payuStatus
     * @return PayUTransaction
     */
    public function setPayuStatus($payuStatus)
    {
        $this->payuStatus = $payuStatus;

        return $this;
    }

    /**
     * @return string
     */
    public function getPayuRc()
    {
        return $this->payuRc;
    }

    /**
     * @param string $payuRc
     * @return PayUTransaction
     */
    public function setPayuRc($payuRc)
    {
        $this->payuRc = $payuRc;

        return $this;
    }

    /**
     * @return string
     */
    public function getPayuRt()
    {
        return $this->payuRt;
    }

    /**
     * @param string $payuRt
     * @return PayUTransaction
     */
    public function setPayuRt($payuRt)
    {
        $this->payuRt = $payuRt;

        return $this;
    }

    /**
     * @return string
     */
    public function getPayuDate()
    {
        return $this->payuDate;
    }

    /**
     * @param string $payuDate
     * @return PayUTransaction
     */
    public function setPayuDate($payuDate)
    {
        $this->payuDate = $payuDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentType()
    {
        return $this->paymentType;
    }

    /**
     * @param string $paymentType
     * @return PayUTransaction
     */
    public function setPaymentType($paymentType)
    {
        $this->paymentType = $paymentType;

        return $this;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     * @return PayUTransaction
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $curency
     * @return PayUTransaction
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     * @return PayUTransaction
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     * @return PayUTransaction
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}