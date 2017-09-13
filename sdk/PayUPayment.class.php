<?php

/**
 *  Copyright (C) 2013 PayU Hungary Kft.
 *
 *  PHP version 5
 *
 *  This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * @category  SDK
 * @package   PayU_SDK
 * @author    Lajos Bacsi <lajos.bacsi@payu.hu>
 * @author    Nandor Szauer <nandor.szauer@payu.com>
 * @copyright 2014 PayU Hungary Kft. (http://www.payu.hu)
 * @license   http://www.gnu.org/licenses/gpl-3.0.html  GNU GENERAL PUBLIC LICENSE (GPL V3.0)
 * @version   2.1.5
 * @link      http://www.payu.hu
 * 
 */
 
require_once 'PayUBase.class.php';

/**
 * PayU LiveUpdate
 *
 * Sending orders via HTTP request
 *
 * @category SDK
 * @package  PayU_SDK
 * @author   Lajos Bacsi <lajos.bacsi@payu.hu>
 * @author   Nandor Szauer <nandor.szauer@payu.com>
 * @license  http://www.gnu.org/licenses/gpl-3.0.html  GNU GENERAL PUBLIC LICENSE (GPL V3.0)
 * @link     http://www.payu.hu
 *
 */
class PayULiveUpdate extends PayUTransaction
{
    protected $hashData = array();
    public $formData = array();
    public $logger = false;

    protected $validFields = array(
        //order
        "MERCHANT" => array("type" => "single", "paramName" => "merchantId", "required" => true),
        "ORDER_REF" => array("type" => "single", "required" => true),
        "ORDER_DATE" => array("type" => "single", "required" => true),
        "ORDER_PNAME" => array("type" => "product", "paramName" => "name", "required" => true),
        "ORDER_PCODE" => array("type" => "product", "paramName" => "code", "required" => true),
        "ORDER_PINFO" => array("type" => "product", "paramName" => "info"),
        "ORDER_PRICE" => array("type" => "product", "paramName" => "price", "required" => true),
        "ORDER_QTY" => array("type" => "product", "paramName" => "qty", "required" => true),
        "ORDER_VAT" => array("type" => "product", "default" => "0", "paramName" => "vat", "required" => true),         
        "PRICES_CURRENCY" => array("type" => "single", "default" => "HUF","required" => true),
        "ORDER_SHIPPING" => array("type" => "single", "default" => "0"),           
        "DISCOUNT" => array("type" => "single", "default" => "0"),            
        "PAY_METHOD" => array("type" => "single", "default" => ""),
        "LANGUAGE" => array("type" => "single", "default" => "HU"),            
        "AUTOMODE" => array("type" => "single", "default" => "1"),  
        "ORDER_TIMEOUT" => array("type" => "single"),
        "TIMEOUT_URL" => array("type" => "single"),
        "BACK_REF" => array("type" => "single"),
        "LU_ENABLE_TOKEN" => array("type" => "single", "required" => false),
        "LU_TOKEN_TYPE" => array("type" => "single", "required" => false),
        
        //billing
        "BILL_FNAME" => array("type" => "single", "required" => true),
        "BILL_LNAME" => array("type" => "single", "required" => true),
        "BILL_COMPANY" => array("type" => "single"),
        "BILL_FISCALCODE" => array("type" => "single"),
        "BILL_EMAIL" => array("type" => "single", "required" => true),
        "BILL_PHONE" => array("type" => "single", "required" => true),
        "BILL_FAX" => array("type" => "single"),
        "BILL_ADDRESS" => array("type" => "single", "required" => true),
        "BILL_ADDRESS2" => array("type" => "single"),
        "BILL_ZIPCODE" => array("type" => "single", "required" => true),
        "BILL_CITY" => array("type" => "single", "required" => true),
        "BILL_STATE" => array("type" => "single", "required" => true),
        "BILL_COUNTRYCODE" => array("type" => "single", "required" => true),
        
        //delivery
        "DELIVERY_FNAME" => array("type" => "single", "required" => true),
        "DELIVERY_LNAME" => array("type" => "single", "required" => true),
        "DELIVERY_COMPANY" => array("type" => "single"),
        "DELIVERY_PHONE" => array("type" => "single", "required" => true),
        "DELIVERY_ADDRESS" => array("type" => "single", "required" => true),
        "DELIVERY_ADDRESS2" => array("type" => "single"),
        "DELIVERY_ZIPCODE" => array("type" => "single", "required" => true),
        "DELIVERY_CITY" => array("type" => "single", "required" => true),
        "DELIVERY_STATE" => array("type" => "single", "required" => true),
        "DELIVERY_COUNTRYCODE" => array("type" => "single", "required" => true),
    );

    //hash fields
    public $hashFields = array(
        "MERCHANT",
        "ORDER_REF",
        "ORDER_DATE",
        "ORDER_PNAME",
        "ORDER_PCODE",
        "ORDER_PINFO",
        "ORDER_PRICE",
        "ORDER_QTY",
        "ORDER_VAT",
        "ORDER_SHIPPING",
        "PRICES_CURRENCY",
        "DISCOUNT",
        "PAY_METHOD"
    );
    
    /**
     * Constructor of PayULiveUpdate class
     * 
     * @param mixed $config Configuration array or filename
     *
     * @return void 
     *
     */
    public function __construct($config)
    {
        $this->setDefaults(
            array(
                $this->validFields
            )
        );
        $this->setup($config);
        $this->fieldData['MERCHANT'] = $this->merchantId;
        $this->targetUrl = $this->luUrl;        
    }

    /**
     * Generates a ready-to-insert HTML FORM
     * 
     * @param string  $formName          The ID parameter of the form
     * @param string  $submitElement     The type of the submit element ('button' or 'link')
     * @param string  $submitElementText The lebel for the submit element
     * @param boolean $data              Data content
     *
     * @return string The created HTML form
     *     
     */
    public function createHtmlForm($formName = 'PayUForm', $submitElement = 'button', $submitElementText = '', $data = array())
    {
        if (!$this->prepareFields("ORDER_HASH")) {
            return false;
        }
        $this->logFunc("LiveUpdate", $this->formData, $this->formData['ORDER_REF']);
        return parent::createHtmlForm($formName, $submitElement, $submitElementText, $this->formData);
    }         
}
    
/**
 * PayU BACK_REF
 * 
 * Processes information sent via HTTP GET on the returning site after a payment
 *
 * @category SDK
 * @package  PayU_SDK
 * @author   Lajos Bacsi <lajos.bacsi@payu.hu>
 * @author   Nandor Szauer <nandor.szauer@payu.com>
 * @license  http://www.gnu.org/licenses/gpl-3.0.html  GNU GENERAL PUBLIC LICENSE (GPL V3.0)
 * @link     http://www.payu.hu
 *
 */
class PayUBackRef extends PayUTransaction
{
    protected $backref;
    protected $request;
    protected $iosConfig = array();
	public $error;
    protected $returnVars = array(
        "RC", 
        "RT", 
        "3dsecure", 
        "date", 
        "payrefno", 
        "ctrl"
    );
    public $backStatusArray = array(
        'BACKREF_DATE' => 'N/A',
        'REFNOEXT' => 'N/A',
        'PAYREFNO' => 'N/A',
        'ORDER_STATUS' => 'N/A',
        'PAYMETHOD' => 'N/A',
        'RESULT' => false
    ); 
    public $successfulStatus = array(
        "IN_PROGRESS",          //card authorized on backref
        "PAYMENT_AUTHORIZED",   //IPN
        "COMPLETE",             //IDN
        "WAITING_PAYMENT",      //waiting for WIRE 
        "CASH"                  //payed on delivery
    );
    public  $unsuccessfulStatus = array(
        "CARD_NOTAUTHORIZED",   //unsuccessful transaction 
        "FRAUD",
        "TEST"
    );
    
    /**
     * Constructor of PayUBackRef class
     * 
     * @param mixed $config Configuration array or filename
     *
     * @return void
     *
     */
    public function __construct($config)
    {
        $this->setup($config);
		$this->iosConfig = $config;
        $this->createRequestUriNotGiven();
        $this->backStatusArray['BACKREF_DATE'] = (isset($this->getData['date'])) ? $this->getData['date'] : 'N/A';
        $this->backStatusArray['REFNOEXT'] = (isset($this->getData['order_ref'])) ? $this->getData['order_ref'] : 'N/A';
        $this->backStatusArray['PAYREFNO'] = (isset($this->getData['payrefno'])) ? $this->getData['payrefno'] : 'N/A';           
    }
       
    /**
     * Creates request URI from HTTP SERVER VARS.
     * Handles http and https
     * 
     * @return void
     *
     */
    protected function createRequestUriNotGiven()
    {
        $protocol = "http://";
        if (isset($this->serverData['HTTP_FRONT_END_HTTPS']) and $this->serverData['HTTP_FRONT_END_HTTPS'] == "On") {
            $protocol = "https://";
        }   
        if (!empty($this->serverData['HTTPS']) && $this->serverData['HTTPS'] !== 'off') {
            $protocol = "https://";
        }       
        if (!empty($this->serverData['HTTP_X_FORWARDED_PROTO']) && $this->serverData['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($this->serverData['HTTP_X_FORWARDED_SSL']) && $this->serverData['HTTP_X_FORWARDED_SSL'] == 'on') {
            $protocol = "https://";
        }      
        $this->request = $protocol . $this->serverData['HTTP_HOST'] . $this->serverData['REQUEST_URI'];
    }
    
    /**
     * Validates CTRL variable
     *
     * @return boolean
     *
     */
    protected function checkCtrl()
    {
        $requestURL = substr($this->request, 0, -38); //the last 38 characters are the CTRL param
        $hashInput = strlen($requestURL).$requestURL;  
        //optional debug info, no need for live payment
        /*if (!$this->debug) {
            echo "\n<br />".$this->request;
            echo "\n<br />".$requestURL;
            echo "\n<br />".$this->hmac($this->secretKey, $hashInput);
            echo "\n<br />".$_GET['ctrl'];
        }*/
        if (isset($this->getData['ctrl']) && $this->getData['ctrl'] == $this->hmac($this->secretKey, $hashInput)) {
            return true;
        }
        return false;
    }
    
    /**
     * Check card authorization response
     *
     * 1. check ctrl
     * 2. check RC & RT 
     * 3. check IOS status
     * 
     * @return boolean
     *
     */
    public function checkResponse() 
    {
        if (!isset($this->order_ref)) {
            $this->error = "Missing order_ref variable";
            return false;
        }
        $this->logFunc("BackRef", $this->getData, $this->order_ref);

        if (!$this->checkCtrl()) {    
            $this->error = "INVALID CTRL";
            return false;
        }
        $this->iosConfig['MERCHANT'] = $this->merchantId;
		$this->iosConfig['SECRET_KEY'] = $this->secretKey;
		$ios = new PayUIos($this->iosConfig, $this->order_ref);
        if (is_object($ios)) {   
            $this->checkIOSStatus($ios);
        }
        $this->logFunc("BackStatus", $this->backStatusArray, $this->order_ref);      
        if (!$this->checkRtVariable($ios)) {
            return false;
        }      
        if (!$this->backStatusArray['RESULT']) {
            return false;
        }
        return true;
    }
 
    /**
     * Check IOS result
     * 
     * @param obj $ios Result of IOS comunication
     *
     * @return boolean
     *
     */    
    protected function checkIOSStatus($ios)
    {
        $this->backStatusArray['ORDER_STATUS'] = (isset($ios->status['ORDER_STATUS'])) ? $ios->status['ORDER_STATUS'] : 'IOS_ERROR';
        $this->backStatusArray['PAYMETHOD'] = (isset($ios->status['PAYMETHOD'])) ? $ios->status['PAYMETHOD'] : 'N/A';
        if (in_array(trim($ios->status['ORDER_STATUS']), $this->successfulStatus)) {
            $this->backStatusArray['RESULT'] = true;
        } elseif (in_array(trim($ios->status['ORDER_STATUS']), $this->unsuccessfulStatus)) {
            $this->backStatusArray['RESULT'] = false;
        }     
    }

    /**
     * Check RT variable
     *
     * @param obj $ios Result of IOS comunication
     * 
     * @return boolean
     *
     */    
    protected function checkRtVariable($ios)
    {
        if (isset($this->getData['RT'])) {    
            //000 and 001, or App are successful
            if (in_array(substr($this->getData['RT'], 0, 3), array("000", "001", "App"))) {
                $this->backStatusArray['RESULT'] = true;        
                //No Response    
            } elseif ($this->getData['RT'] == "No Response" || $this->getData['RC'] == "NR") {
                $this->backStatusArray['RESULT'] = true;
                //empty return data 
            } elseif ($this->getData['RT'] == "") {
                //check IOS ORDER_STATUS
                if (in_array(trim($ios->status['ORDER_STATUS']), $this->successfulStatus)) {
                    $this->backStatusArray['RESULT'] = true;
                    return true;
                }
            }                       
        }        
        if (!isset($this->getData['RT'])) {      
            $this->error = "MISSING VARIABLES";
            $this->backStatusArray['RESULT'] = false;
            return false;             
        }    
        return true;
    }
    
}


/**
 * PayU Instant Payment Notification
 *
 * Processes notifications sent via HTTP POST request
 *
 * @category SDK
 * @package  PayU_SDK
 * @author   Lajos Bacsi <lajos.bacsi@payu.hu>
 * @author   Nandor Szauer <nandor.szauer@payu.com>
 * @license  http://www.gnu.org/licenses/gpl-3.0.html  GNU GENERAL PUBLIC LICENSE (GPL V3.0)
 * @link     http://www.payu.hu
 *
 */
class PayUIpn extends PayUBase
{
    public $echo = false;
    public $successfulStatus = array(
        "PAYMENT_AUTHORIZED",   //IPN
        "COMPLETE",             //IDN
        "REFUND",               //IRN
        "PAYMENT_RECEIVED",     //WIRE
        "CASH",                 //CASH
    );

    /**
     * Constructor of PayUIpn class
     * 
     * @param mixed $config Configuration array or filename
     *
     * @return void
     *
     */
    public function __construct($config = array())
    {
        $this->setup($config);
    }
    
    /**
     * Validate recceived data against HMAC HASH
     *
     * @return boolean
     * 
     */
    public function validateReceived()
    {       
        $this->logFunc("IPN", $this->postData, $this->postData['REFNOEXT']);
		
        if (!in_array(trim($this->postData['ORDERSTATUS']), $this->successfulStatus)) {   
            return false;
        }
        if ($this->createHashString($this->flatArray($this->postData, array("HASH"))) == $this->postData['HASH']) {
            return true;
        }
        return false;
    }
    
    /**
     * Creates INLINE string for corfirmation
     * 
     * @return string $string <EPAYMENT> tag
     *
     */
    public function confirmReceived()
    {
        $serverDate = date("YmdHis");
        $hashArray = array(
            $this->postData['IPN_PID'][0],
            $this->postData['IPN_PNAME'][0],
            $this->postData['IPN_DATE'],
            $serverDate
        );
        $hash = $this->createHashString($hashArray);       
        $string = "<EPAYMENT>".$serverDate."|".$hash."</EPAYMENT>";           
        if ($this->echo) {
            echo $string;
        }
        return $string;
    }
}


/**
 * PayUIOS
 * 
 * Helper object containing information about a product
 *
 * @category SDK
 * @package  PayU_SDK
 * @author   Lajos Bacsi <lajos.bacsi@payu.hu>
 * @author   Nandor Szauer <nandor.szauer@payu.com>
 * @license  http://www.gnu.org/licenses/gpl-3.0.html  GNU GENERAL PUBLIC LICENSE (GPL V3.0)
 * @link     http://www.payu.hu
 *
 */
class PayUIos extends PayUTransaction
{
    protected $orderNumber;
    protected $hash;
    protected $merchantId;
    protected $orderStatus;
    protected $maxRun = 10;
	protected $iosOrderUrl = '';
    public $status = Array();    
         
    /**
     * Constructor of PayUIos class
     * 
     * @param mixed  $config      Configuration array or filename
     * @param string $orderNumber External number of the order
     *
     * @return void
     *
     */
    public function __construct($config = array(), $orderNumber = '0')
    {
        $this->setup($config);       
        $hashArray = array(
            $this->merchantId,
            $orderNumber
        );
        $this->orderNumber = $orderNumber;
		$this->iosOrderUrl = $this->defaultsData['BASE_URL'] . $this->defaultsData['IOS_URL'];
        $this->hash = $this->createHashString($hashArray);      
        $this->runIos();        
    }

    /**
     * Starts IOS communication
     * 
     * @return void 
     *
     */  
    public function runIos()
    {
        $iosArray = array('MERCHANT' => $this->merchantId, 'REFNOEXT' => $this->orderNumber, 'HASH' => $this->hash);      
        $iosCounter = 0;
        while ($iosCounter < $this->maxRun) {
            $result = $this->startRequest($this->iosOrderUrl, $iosArray, 'POST');  
			$this->status['ORDER_STATUS'] = 'N/A';
			if (!empty($result)) {
				$dom = new DOMDocument;
				$dom->loadXML($result);    
				$order = $dom->getElementsByTagName("Order");
				foreach ($order->item(0)->childNodes as $item) {
					if ($item->nodeType == 1) {
						$this->status[$item->tagName] = $item->nodeValue;
					}
				} 				
			}
            switch ($this->status['ORDER_STATUS']) {
            case 'NOT_FOUND': 
                $iosCounter++;
                sleep(1);
                break;
            case 'CARD_NOTAUTHORIZED': 
                $iosCounter += 5;
                sleep(1);
                break;               
            default:
                $iosCounter += $this->maxRun;
            }
        }   
    }        
}


/**
 * PayUProduct
 * 
 * Helper object containing information about a product
 *
 * @category SDK
 * @package  PayU_SDK
 * @author   Lajos Bacsi <lajos.bacsi@payu.hu>
 * @author   Nandor Szauer <nandor.szauer@payu.com>
 * @license  http://www.gnu.org/licenses/gpl-3.0.html  GNU GENERAL PUBLIC LICENSE (GPL V3.0)
 * @link     http://www.payu.hu
 *
 */
class PayUProduct
{
    public $name;
    public $group;
    public $code;
    public $info;
    public $price;
    public $qty;
    public $vat;
    public $ver;
    
    /**
     * Constructor for PayUProduct
     * 
     * Creates an object for a product for later processing
     * 
     * @param array $productParams Sets object properties according to variables passed in this array
     *
     * @return void
     */
    public function __construct($productParams = array())
    {
        $denied = array("'", "\\", "\"");
        foreach ($productParams as $var => $param) {
            if (property_exists($this, $var)) {            
                $param = str_replace($denied, '', $param);           
                $this->$var = $param;
            }
        }
    }
}

?>
