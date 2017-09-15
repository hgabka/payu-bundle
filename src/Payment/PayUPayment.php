<?php
/**
 * Created by PhpStorm.
 * User: sfhun
 * Date: 2017.09.13.
 * Time: 21:07
 */

namespace Hgabka\PayUBundle\Payment;

use Hgabka\PayUBundle\Factory\PaymentFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class PayUPayment
{
    /** @var array */
    protected $payUData = [];

    /** @var \PayULiveUpdate */
    protected $liveUpdate;

    /** @var  RouterInterface */
    protected $router;

    /** @var PaymentFactory */
    protected $factory;

    public function __construct(PaymentFactory $factory)
    {
        $this->factory = $factory;
        $this->router = $factory->getRouter();
        $this->setDefaults($factory->getConfig());
        $this->liveUpdate = new  \PayULiveUpdate($this->payUData);
    }

    /**
     * @param array $config
     */
    protected function setDefaults(array $config)
    {
        $this->payUData = [
            'MERCHANT'         => $config['merchant'],
            'SECRET_KEY'       => $config['secret'],
            'METHOD'           => "",
            'ORDER_DATE'       => date("Y-m-d H:i:s"),
            'LOGGER'           => $config['logging'],
            'LOG_PATH'         => $config['log_path'],
            'LANGUAGE'         => 'HU',
            'PRICES_CURRENCY'  => 'HUF',
            'DISCOUNT'         => 0,
            'ORDER_PRICE_TYPE' => 'GROSS',
            'ORDER_SHIPPING'   => 0,
            'MIGRATION'        => $config['migration'],
            'SANDBOX'          => $config['sandbox'],
            'CURL'             => true,
            'ORDER_TIMEOUT'    => 300,
            'GET_DATA'         => $_GET,
            'POST_DATA'        => $_POST,
            'SERVER_DATA'      => $_SERVER,
        ];
    }

    public function __call($method, $arguments)
    {
        if (!in_array($verb = substr($method, 0, 3), ['set', 'get'])) {
            throw new \Exception('Ismeretlen, vagy nem elerheto metodus: ' . get_class($this) . '::' . $method);
        }

        $property = substr($method, 3);

        // Ha setter, és nincs érték
        if ($verb == 'set') {
            if (!array_key_exists(0, $arguments)) {
                throw new \Exception('Hianyzo parameter a ' . get_class($this) . '::' . $method . ' hivasnal');
            }

            return $this->set($property, $arguments[0]);
        }

        return $this->get($property);
    }

    public function setBackrefRoute($route)
    {
        return $this->setBackRef($this->router->generate($route, [], UrlGeneratorInterface::ABSOLUTE_URL));
    }

    public function setTimeoutRoute($route)
    {
        return $this->setTimeoutUrl($this->router->generate($route, [], UrlGeneratorInterface::ABSOLUTE_URL));
    }

    protected function get($name)
    {
        $prop = $this->checkProperty($name);

        return is_null($prop) ? null : $this->payUData[$prop];
    }

    protected function set($name, $value)
    {
        $name = strtoupper(sfInflector::tableize($name));
        $this->payUData[$name] = $value;

        return $this;
    }

    protected function checkProperty($name)
    {
        if (array_key_exists($name, $this->payUData)) {
            return $name;
        }

        if (array_key_exists(strtoupper($this->factory->tableize($name)), $this->payUData)) {
            return strtoupper($this->factory->tableize($name));
        }

        return null;
    }

    public function addItem($name, $code, $description, $price, $quantity = 1, $vat = 0)
    {
        $this->liveUpdate->addProduct([
            'name'  => $name,
            'code'  => $code,
            'info'  => $description,
            'price' => $price,
            'qty'   => $quantity,
            'vat'   => $vat,
        ]);
    }

    protected function setData()
    {
        $this->liveUpdate->logger = $this->payUData['LOGGER'];

        $this->liveUpdate->log_path = $this->payUData['LOG_PATH'];

        if (array_key_exists('METHOD', $this->payUData)) {
            if (empty($this->payUData['METHOD'])) {
                $this->liveUpdate->setField("PAY_METHOD", '');
                $this->liveUpdate->setField("AUTOMODE", 0);
            } else {
                $this->liveUpdate->setField("PAY_METHOD", $this->payUData['METHOD']);
                $this->liveUpdate->setField("AUTOMODE", 1);
            }
        }

        foreach ($this->payUData as $key => $data) {
            if (in_array($key, ['LOGGER', 'LOG_PATH', 'METHOD'])) {
                continue;
            }

            $this->liveUpdate->setField($key, $data);
        }
    }

    public function getForm($submitElement = 'auto', $submitElementText = false)
    {
        $this->setData();

        return $this->liveUpdate->createHtmlForm('payuform', $submitElement, $submitElementText);
    }

    protected function lcfirst($string)
    {
        return substr_replace($string, strtolower(substr($string, 0, 1)), 0, 1);
    }

    public function handleBackref()
    {
        $backRef = new \PayUBackRef($this->payUData);
        $backRef->order_ref = $_REQUEST['order_ref'];
        $backRef->logger = $this->payUData['LOGGER'];
        $backRef->log_path = $this->payUData['LOG_PATH'];

        $check = $backRef->checkResponse();
        $backStatus = $backRef->backStatusArray;

        $transaction = $this->factory->createOrGetTransaction($_REQUEST, $backStatus, $check);

        return ['success' => $check, 'transaction' => $transaction, 'response' => $backStatus, 'order_id' => $_REQUEST['order_ref']];
    }

    public function handleTimeout()
    {
        $transaction = $this->factory->createOrGetTransaction($_REQUEST, null);

        return ['transaction' => $transaction, 'order_id' => $_REQUEST['order_ref']];
    }

    public function handleIpn($params)
    {
        $ipn = new \PayUIpn($this->payUData);
        $ipn->logger = $this->payUData['LOGGER'];
        $ipn->log_path = $this->payUData['LOG_PATH'];

        if (!$ipn->validateReceived()) {
            return false;
        }

        $transaction = $this->factory->createOrGetTransactionFromIpn($params);

        return ['transaction' => $transaction, 'response' => $ipn->confirmReceived()];
    }
}