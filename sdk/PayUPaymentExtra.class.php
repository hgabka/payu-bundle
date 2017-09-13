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
 * PayU Instant Delivery Information
 *
 * Sends delivery notification via HTTP
 *
 * @category SDK
 * @package  PayU_SDK
 * @author   Lajos Bacsi <lajos.bacsi@payu.hu>
 * @author   Nandor Szauer <nandor.szauer@payu.com>
 * @license  http://www.gnu.org/licenses/gpl-3.0.html  GNU GENERAL PUBLIC LICENSE (GPL V3.0)
 * @link     http://www.payu.hu
 *
 */
class PayUIdn extends PayUTransaction
{
	public $targetUrl = '';
    public $missing = array();
    public $hashFields = array(
        "MERCHANT",
        "ORDER_REF",
        "ORDER_AMOUNT",
        "ORDER_CURRENCY",
        "IDN_DATE"
    );
    
    protected $validFields = array(
        "MERCHANT" => array("type"=>"single", "paramName"=>"merchantId", "required" => true),
        "ORDER_REF" => array("type"=>"single", "paramName"=>"orderRef", "required"=>true),
        "ORDER_AMOUNT" => array("type"=>"single", "paramName"=>"amount", "required"=>true),
        "ORDER_CURRENCY" => array("type"=>"single", "paramName"=>"currency", "required"=>true),
        "IDN_DATE" => array("type"=>"single", "paramName"=>"idnDate", "required"=>true),
        "REF_URL" => array("type"=>"single", "paramName"=>"refUrl"),
    );

    /**
     * Constructor of PayUIdn class
     * 
     * @param mixed $config Configuration array or filename
     *
     * @return void
     *
     */
    public function __construct($config = array())
    {
        $this->setup($config);
        $this->fieldData['MERCHANT'] = $this->merchantId;
		$this->targetUrl = $this->defaultsData['BASE_URL'].$this->defaultsData['IDN_URL'];
    }

    /**
     * Creates associative array for the received data
     * 
     * @param array $data Processed data
     *
     * @return void
     *
     */
    protected function nameData($data = array())
    {
        return array(
            "ORDER_REF" => (isset($data[0]))?$data[0]:'N/A',
            "RESPONSE_CODE" => (isset($data[1]))?$data[1]:'N/A',
            "RESPONSE_MSG" => (isset($data[2]))?$data[2]:'N/A',
            "IDN_DATE" => (isset($data[3]))?$data[3]:'N/A',
            "ORDER_HASH" => (isset($data[4]))?$data[4]:'N/A',
        );
    }
    
    /**
     * Sends notification via cURL
     * 
     * @param array $data Data array to be sent
     *
     * @return array $this->nameData() Result
     *
     */
    public function requestIdn($data = array())
    {
        if (count($data) == 0) {
             return $this->nameData();
        }
        $idnHash = $this->createHashString($data);
        $data['ORDER_HASH'] = $idnHash;
        $result = $this->startRequest($this->targetUrl, $data, 'POST');
        if (is_string($result)) {
            $data = explode("|", $result);    
            return $this->nameData($data);            
        }
        $this->logFunc("IDN", $result, $result['ORDER_REF']);
    }                                      
     
}


/**
 * PayU Instant Refund Notification
 *
 * Sends Refund request via HTTP request
 * 
 * @category SDK
 * @package  PayU_SDK
 * @author   Lajos Bacsi <lajos.bacsi@payu.hu>
 * @author   Nandor Szauer <nandor.szauer@payu.com>
 * @license  http://www.gnu.org/licenses/gpl-3.0.html  GNU GENERAL PUBLIC LICENSE (GPL V3.0)
 * @link     http://www.payu.hu
 *
 */
class PayUIrn extends PayUTransaction
{
	public $targetUrl = '';
    public $missing = array();
    public $hashFields = array(
        "MERCHANT",
        "ORDER_REF",
        "ORDER_AMOUNT",
        "ORDER_CURRENCY",
        "IRN_DATE",
        "ORDER_PCODE",
        "ORDER_QTY",
        "AMOUNT"
    );
    
    protected $validFields = array(
        "MERCHANT" => array("type" => "single", "paramName" => "merchantId", "required" => true),
        "ORDER_REF" => array("type" => "single", "paramName" => "orderRef", "required" => true),
        "ORDER_AMOUNT" => array("type" => "single", "paramName" => "amount", "required" => true),
        "AMOUNT" => array("type" => "single", "paramName" => "amount", "required" => true),
        "ORDER_CURRENCY" => array("type" => "single", "paramName" => "currency", "required" => true),
        "IRN_DATE" => array("type" => "single", "paramName" => "irnDate", "required" => true),
        "REF_URL" => array("type" => "single", "paramName" => "refUrl"),
        "ORDER_PCODE" => array("type" => "product", "paramName" => "code", "rename"=>"PRODUCTS_IDS"),
        "ORDER_QTY" => array("type" => "product", "paramName" => "qty", "rename" => "PRODUCTS_QTY")
    );
        
    /**
     * Constructor of PayUIrn class
     * 
     * @param mixed $config Configuration array or filename
     *
     * @return void
     *
     */
    public function __construct($config = array())
    {
        $this->setup($config);
        $this->fieldData['MERCHANT'] = $this->merchantId;
		$this->targetUrl = $this->defaultsData['BASE_URL'].$this->defaultsData['IRN_URL'];
    }

    /**
     * Creates associative array for the received data
     * 
     * @param array $data Processed data
     *
     * @return void
     *
     */
    protected function nameData($data = array())
    {
        return array(
            "ORDER_REF" => (isset($data[0])) ? $data[0] : 'N/A',
            "RESPONSE_CODE" => (isset($data[1])) ? $data[1] : 'N/A',
            "RESPONSE_MSG" => (isset($data[2])) ? $data[2] : 'N/A',
            "IRN_DATE" => (isset($data[3])) ? $data[3] : 'N/A',
            "ORDER_HASH" => (isset($data[4])) ? $data[4] : 'N/A',
        );
    }
    
    /**
     * Sends notification via cURL
     * 
     * @param array $data (Optional) Data array to be sent
     *
     * @return array $this->nameData() Result
     *
     */
    public function requestIrn($data = array())
    {
        if (count($data) == 0) {
             return $this->nameData();
        }
        $irnHash = $this->createHashString($data);
        $data['ORDER_HASH'] = $irnHash;
        $result = $this->startRequest($this->targetUrl, $data, 'POST');
        if (is_string($result)) {
            $data = explode("|", $result);    
            return $this->nameData($data);            
        }
        $this->logFunc("IRN", $result, $result['ORDER_REF']);
    }         
    
}

?>