<?php
/**
 * Created by PhpStorm.
 * User: sfhun
 * Date: 2017.09.13.
 * Time: 21:01
 */

namespace Hgabka\PayUBundle\Factory;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Hgabka\PayUBundle\Event\PayUEvent;
use Hgabka\PayUBundle\Payment\PayUPayment;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Hgabka\PayUBundle\Entity\PayUTransaction as TransactionEntity;

class PaymentFactory
{
    const STATUS_PENDING = 'PENDING';
    const STATUS_SUCCESS = 'SUCCESS';
    const STATUS_CANCEL = 'CANCEL';

    /** @var  array */
    protected $config;

    /** @var  RouterInterface */
    protected $router;

    /** @var  Registry $doctrine */
    protected $doctrine;

    /** @var  EventDispatcherInterface $dispatcher */
    protected $dispatcher;

    /**
     * PaymentFactory constructor.
     * @param Registry $doctrine
     * @param RouterInterface $router
     * @param EventDispatcherInterface $dispatcher
     * @param array $config
     */
    public function __construct(Registry $doctrine, RouterInterface $router, EventDispatcherInterface $dispatcher, array $config)
    {
        $this->doctrine = $doctrine;
        $this->router = $router;
        $this->config = $config;
        $this->dispatcher = $dispatcher;
    }

    public function createPayment()
    {
        return new PayUPayment($this);
    }

    public function createOrGetTransaction($params, $statusArray, $check = false)
    {
        $orderId = $params['order_ref'];

        if (!$orderId) {
            throw new \Exception('Ures order id');
        }

        $transaction = $this->doctrine
            ->getRepository('HgabkaPayUBundle:PayUTransaction')
            ->createQueryBuilder('t')
            ->where('t.ShopOrderId = :orderid')
            ->setParameter('orderid', $orderId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (!$transaction) {
            $transaction = new TransactionEntity();
        }

        if (is_null($transaction->getId()) || $transaction->getState() != self::STATUS_SUCCESS) {
            $transaction->setShopOrderId($orderId);
            if (isset($params['RC'])) {
                $transaction->setPayuRc($params['RC']);
            }

            if (isset($params['RT'])) {
                $transaction->setPayuRt($params['RT']);
            }

            if (isset($params['date'])) {
                $transaction->setPayuDate($params['date']);
            }

            if (!$statusArray) {
                $transaction->setState(self::STATUS_CANCEL);
            } else {
                $transaction->setState($check ? self::STATUS_PENDING : self::STATUS_CANCEL);
                $transaction->setPayuRefno($statusArray['PAYREFNO']);
                $transaction->setPaymentType($statusArray['PAYMETHOD']);
                $transaction->setPayuStatus($statusArray['ORDER_STATUS']);
            }
        }

        $em = $this->doctrine->getManager();
        $em->persist($transaction);
        $em->flush();

        $event = new PayUEvent();
        $event
            ->setParameters($_REQUEST)
            ->setResponse($statusArray)
            ->setTransaction($transaction)
            ->setOrderId($_REQUEST['order_ref'])
        ;

        $this->dispatcher->dispatch(PayUEvent::EVENT_TRANSACTION_CREATED, $event);

        return $transaction;
    }

    public function createOrGetTransactionFromIpn($params)
    {
        $orderId = isset($params['REFNOEXT']) ? $params['REFNOEXT'] : null;

        if (!$orderId) {
            throw new \Exception('Ures order id');
        }

        $transaction = $this->doctrine
            ->getRepository('HgabkaPayUBundle:PayUTransaction')
            ->createQueryBuilder('t')
            ->where('t.ShopOrderId = :orderid')
            ->setParameter('orderid', $orderId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (!$transaction) {
            $transaction = new TransactionEntity();
        }

        $transaction->setState(self::STATUS_SUCCESS);
        $transaction->setPayuDate($params['SALEDATE']);
        $transaction->setPaymentType($params['PAYMETHOD']);
        $transaction->setCurrency($params['CURRENCY']);
        $transaction->setAmount($params['IPN_TOTALGENERAL']);
        $transaction->setPayuStatus($params['ORDERSTATUS']);
        $transaction->setPayuRefno($params['REFNO']);
        $transaction->setShopOrderId($orderId);

        $em = $this->doctrine->getManager();
        $em->persist($transaction);
        $em->flush();

        $event = new PayUEvent();
        $event
            ->setParameters([])
            ->setResponse($params)
            ->setTransaction($transaction)
            ->setOrderId($params['REFNOEXT'])
        ;

        $this->dispatcher->dispatch(PayUEvent::EVENT_PAYMENT_CONFIRMED, $event);

        return $transaction;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @return RouterInterface
     */
    public function getRouter(): RouterInterface
    {
        return $this->router;
    }

    /**
     * Returns subject replaced with regular expression matchs
     *
     * @param mixed $search subject to search
     * @param array $replacePairs array of search => replace pairs
     */
    protected function pregtr($search, $replacePairs)
    {
        foreach ($replacePairs as $pattern => $replacement) {
            if (preg_match('/(.*)e$/', $pattern, $matches)) {
                $pattern = $matches[1];
                $search = preg_replace_callback($pattern, function ($matches) use ($replacement) {
                    preg_match("/('::'\.)?([a-z]*)\('\\\\([0-9]{1})'\)/", $replacement, $match);

                    return ($match[1] == '' ? '' : '::') . call_user_func($match[2], $matches[$match[3]]);
                }, $search);
            } else {
                $search = preg_replace($pattern, $replacement, $search);
            }
        }

        return $search;
    }

    /**
     * Returns a camelized string from a lower case and underscored string by replaceing slash with
     * double-colon and upper-casing each letter preceded by an underscore.
     *
     * @param  string $lower_case_and_underscored_word String to camelize.
     *
     * @return string Camelized string.
     */
    public function camelize($lower_case_and_underscored_word)
    {
        return $this->pregtr($lower_case_and_underscored_word, ['#/(.?)#e' => "'::'.strtoupper('\\1')", '/(^|_|-)+(.)/e' => "strtoupper('\\2')"]);
    }

    /**
     * Returns an underscore-syntaxed version or the CamelCased string.
     *
     * @param  string $camel_cased_word String to underscore.
     *
     * @return string Underscored string.
     */
    public function underscore($camel_cased_word)
    {
        $tmp = $camel_cased_word;
        $tmp = str_replace('::', '/', $tmp);
        $tmp = $this->pregtr($tmp, [
            '/([A-Z]+)([A-Z][a-z])/' => '\\1_\\2',
            '/([a-z\d])([A-Z])/'     => '\\1_\\2',
        ]);

        return strtolower($tmp);
    }

    /**
     * Returns corresponding table name for given classname.
     *
     * @param  string $class_name  Name of class to get database table name for.
     *
     * @return string Name of the databse table for given class.
     */
    public function tableize($class_name)
    {
        return $this->underscore($class_name);
    }
}
