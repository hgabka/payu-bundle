<?php

namespace Hgabka\PayUBundle\Controller;

use Hgabka\PayUBundle\Payment\PayUPayment;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PayUController extends Controller
{
    /**
     * @Route("/confirm", name="hgabka_payu_payment_confirm")
     */
    public function confirmAction(Request $request)
    {
        $params = $request->request->all();

        /** @var PayUPayment $payment */
        $payment = $this->get('hgabka_payu.payment_factory')->createPayment();

        $result = $payment->handleIpn($params);

        $response = new Response();
        if (false !== $result) {
            $response->setContent($result['response']);
        }

        return $response;
    }
}
