<?php

/**
 *  Copyright (C) 2015 PayU Hungary Kft.
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
 * @copyright 2015 PayU Hungary Kft. (http://www.payu.hu)
 * @license   http://www.gnu.org/licenses/gpl-3.0.html  GNU GENERAL PUBLIC LICENSE (GPL V3.0)
 * @version   2.1.5
 * @link      http://www.payu.hu
 * 
 */

/**
 * Modify config data
 *
 * @category SDK
 * @package  PayU_SDK
 * @author   Lajos Bacsi <lajos.bacsi@payu.hu>
 * @author   Nandor Szauer <nandor.szauer@payu.com>
 * @license  http://www.gnu.org/licenses/gpl-3.0.html  GNU GENERAL PUBLIC LICENSE (GPL V3.0)
 * @link     http://www.payu.hu
 * 
 */ 
class PayUModifyConfig extends PayUBase
{
    public $config = array();
    public $debug = false;
    public $debugMessage = '';
    public $error = '';

    /**
     * Constructor of PayUModifyConfig class
     * 
     * @param array $config Configuration array 
     *
     * @return void
     *
     */
    public function __construct($config = array())
    {      
        if (!is_array($config)) {
            $this->error = "config is not array!";
            $this->report();
        } 
        if (count($config) == 0) {
            $this->error = "Empty config array!";
            $this->report();
        }      
        $this->config = $config;
    }

    /**
     * Initialize MERCHANT, SECRET_KEY and CURRENCY
     * 
     * @param string $currency Currency
     * 
     * @return array $this->config Initialized config array
     * 
     */ 
    public function merchantByCurrency($currency = 'HUF')
    {      
        $this->config['CURRENCY'] = str_replace(' ', '', $currency);  
        $merchant = (isset($this->config[$currency.'_MERCHANT'])) ? $this->config[$currency.'_MERCHANT'] : 'MISSING_MERCHANT' ;
		$this->config['MERCHANT'] = str_replace(' ', '', $merchant);
        $secret_key = (isset($this->config[$currency.'_SECRET_KEY'])) ? $this->config[$currency.'_SECRET_KEY'] : 'MISSING_SECRET_KEY'; 
		$this->config['SECRET_KEY'] = str_replace(' ', '', $secret_key);
        if ($this->debug) {
            $this->debugMessage = "<br><pre>CONFIG ARRAY<br>" . print_r($this->config, true) . "</pre><br>";
            $this->report();
        }
        return $this->config;       
    }         
}

/**
 * Base class for PayU implementation
 *
 * @category SDK
 * @package  PayU_SDK
 * @author   Lajos Bacsi <lajos.bacsi@payu.hu>
 * @author   Nandor Szauer <nandor.szauer@payu.com>
 * @license  http://www.gnu.org/licenses/gpl-3.0.html  GNU GENERAL PUBLIC LICENSE (GPL V3.0)
 * @link     http://www.payu.hu
 *
 */
class PayUBase
{
    protected $merchantId;
    protected $secretKey;
    protected $hashCode;
    protected $hashString;
    protected $hashData = array();
    protected $version = 'PHP_2.1.5_M_sdk20151012';
    public $debug = false;
    public $logger = false;
    public $logPath = "log";
    public $hashFields = array();
    public $debugMessage = '';
    public $error = '';
    public $getData = array();
    public $postData = array();
    public $serverData = array();
    public $curl;
    public $defaultsData = array(
        'BASE_URL' => "https://secure.payu.hu/", //please add TRAILING SLASH
		'MIGRATION_LIVE_URL' => 'https://secure.simplepay.hu/payment/',
		'MIGRATION_SANDBOX_URL' => 'https://sandbox.simplepay.hu/payment/',
        'LU_URL' => "order/lu.php",   //relative to BASE_URL
        'ALU_URL' => "order/alu.php", //relative to BASE_URL
        'IDN_URL' => "order/idn.php", //relative to BASE_URL
        'IRN_URL' => "order/irn.php", //relative to BASE_URL
        'IOS_URL' => "order/ios.php", //relative to BASE_URL
        'OC_URL' => "order/tokens/"   //relative to BASE_URL
    );
    public $settings = array(
        'MERCHANT' => 'merchantId',
        'SECRET_KEY' => 'secretKey',
        'BASE_URL' => 'baseUrl',
        'ALU_URL' => 'aluUrl',
        'LU_URL' => 'luUrl',
        'IOS_URL' => 'iosUrl',
        'IDN_URL' => 'idnUrl',
        'IRN_URL' => 'irnUrl',
        'OC_URL' => 'ocUrl',
		'LOG_PATH' => 'logPath'
    );
               
    /**
     * Constructor of Base class
     * 
     * @return void
     * 
     */
    public function __construct()
    {   

    }

    /**
     * Initial settings
     * 
     * @param array $config Array with config options
     *
     * @return boolean
     *
     */    
    public function setup($config = array())
    {   
        if (!is_array($config)) {
            $this->error = "config is not array!";
            $this->report();
            return false;
        }   
        if (count($config) == 0) {  
            $this->error =  "Empty config array!";
            $this->report();
            return false;           
        }
		if (isset($config['MIGRATION'])) {
			if ($config['MIGRATION']) {						
				$this->defaultsData['BASE_URL'] = $this->defaultsData['MIGRATION_LIVE_URL'];				
				if (isset($config['SANDBOX'])) {
					if ($config['SANDBOX']) {
						$this->defaultsData['BASE_URL'] = $this->defaultsData['MIGRATION_SANDBOX_URL'];
					}
				}
			}				
		}
		
        $this->processConfig($this->defaultsData);

        $this->processConfig($config); 
        return true;       
    }
    
    /**
     * Set config options
     * 
     * @param array $config Array with config options
     *
     * @return void
     *
     */
    public function processConfig($config)
    {
        foreach ($config as $setting => $value) {
            if (array_key_exists($setting, $this->settings)) {
                $prop = $this->settings[$setting];
                $this->$prop = $config[$setting];
            }
            switch ($setting) {
            case 'GET_DATA':
                $this->getData = $value;
                break;
            case 'POST_DATA':
                $this->postData = $value;
                break;
            case 'SERVER_DATA':
                $this->serverData = $value;
                break; 
            case 'CURL':
                $this->curl = $value;
                break; 
            }
        }
    }
   
    /**
     * HMAC HASH creation
     * RFC 2104
     * http://www.ietf.org/rfc/rfc2104.txt
     * 
     * @param string $key  Secret key for encryption
     * @param string $data String to encode
     *
     * @return string HMAC hash
     *
     */
    protected function hmac($key, $data)
    {
        $byte = 64; // byte length for md5
        if (strlen($key) > $byte) {
            $key = pack("H*", md5($key));
        }
        $key = str_pad($key, $byte, chr(0x00));
        $ipad = str_pad('', $byte, chr(0x36));
        $opad = str_pad('', $byte, chr(0x5c));
        $kIpad = $key ^ $ipad;
        $kOpad = $key ^ $opad;
        return md5($kOpad . pack("H*", md5($kIpad . $data)));
    }
    
    /**
     * Create HASH code for an array (1-dimension only)
     * 
     * @param array $hashData Array of ordered fields to be HASH-ed
     *
     * @return string Hash code
     *
     */
    protected function createHashString($hashData)
    {    
        $hashString = '';
        foreach ($hashData as $field) {
            if (is_array($field)) {
                $this->error = "No multi-dimension array allowed!";
                return false;
            }
            $hashString .= strlen(StripSlashes($field)).$field;
        }
        $this->hashString = $hashString;
        $this->hashCode = $this->hmac($this->secretKey, $this->hashString);
        return $this->hashCode;       
    }
     
    /**
     * Creates a 1-dimension array from a 2-dimension one
     * 
     * @param array $array Array to be processed
     * @param array $skip  Array of keys to be skipped when creating the new array
     * 
     * @return array $return Flat array
     * 
     */
    public function flatArray($array = array(), $skip = array())
    {
        $return = array();
        foreach ($array as $name => $item) {
            if (!in_array($name, $skip)) {
                if (is_array($item)) {
                    foreach ($item as $subItem) {
                        $return[] = $subItem;
                    }
                } elseif (!is_array($item)) {
                    $return[] = $item;
                }
            }
        }
        return $return;
    }
    
    /**
     * Write log
     * 
     * @param string $state   State of the payment process
     * @param array  $data    Data of the log
     * @param string $orderId External ID of order
     * 
     * @return void
     * 
     */ 
    public function logFunc($state = '', $data = array(), $orderId = 0)
    {
        if ($this->logger) {
            $date = @date('Y-m-d H:i:s', time());
            $logtext = "";
            foreach ($data as $logkey => $logvalue) {           
                if (is_array($logvalue)) {
                    foreach ($logvalue as $subkey => $subvalue) {
                        $logtext .= $orderId.' '.$state.' '.$date.' '.$logkey.'='.$subvalue."\n";
                    }
                } elseif (!is_array($logvalue)) {
                    $logtext .= $orderId.' '.$state.' '.$date.' '.$logkey.'='.$logvalue."\n";
                }               
            }  
		
            file_put_contents($this->logPath.'/'. @date('Ymd', time()).'.log', $logtext, FILE_APPEND | LOCK_EX); 
        }
    }

    /**
     * Returns a list of errors if there was any
     * 
     * @return array $this->error Errors
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Public report
     *
     * @return void
     *
     */
    public function report()
    {      
        print "DEBUG";
        print $this->debugMessage;
        print "<br>";
        print "ERROR";
        print $this->error;
        print "<br>";
        //exit;
    }
        
    /**
     * Version of SDK
     *
     * @return string SDK version
     *
     */
    public function sdkVersion()
    {
        return $this->version;
    }       
}

/**
 * Class for PayU transaction handling
 *
 * @category SDK
 * @package  PayU_SDK
 * @author   Lajos Bacsi <lajos.bacsi@payu.hu>
 * @author   Nandor Szauer <nandor.szauer@payu.com>
 * @license  http://www.gnu.org/licenses/gpl-3.0.html  GNU GENERAL PUBLIC LICENSE (GPL V3.0)
 * @link     http://www.payu.hu
 *
 */
class PayUTransaction extends PayUBase
{
    public $result;
    public $targetUrl;
    public $baseUrl;
    protected $products = array();    
    public $formData = array();
    public $fieldData = array();
    protected $missing = array();
   
    /**
     * Constructor of PayUTransaction class
     * 
     * @return void
     * 
     */    
    public function __construct()
    {

    }
    
    /**
     * Sends a HTTP request via cURL or file_get_contents() and returns the response
     *
     * @param string $url    Base URL for request
     * @param array  $data   Parameters to send
     * @param string $method Request method
     * 
     * @return array $result Response
     * 
     */
    public function startRequest($url = '', $data = array(), $method = 'POST')
    {      
        if (!$this->curl) { 
            //XML content
            if (in_array("libxml",  get_loaded_extensions())) {
                $options = array(
                    'http' => array(
                    'method' => $method,
                    'header' =>
                        "Accept-language: en\r\n".
                        "Content-type: application/x-www-form-urlencoded\r\n",
                    'content' => http_build_query($data, '', '&')
                ));
                $context = stream_context_create($options);
                $result = file_get_contents($url, false, $context);    
                return $result;
            }
        } elseif ($this->curl) {
            //cURL 
            if (in_array("curl",  get_loaded_extensions())) {
                
                $curlData = curl_init();
                curl_setopt($curlData, CURLOPT_URL, $url);
                if ($method == "POST") {
                    curl_setopt($curlData, CURLOPT_POST, true);
                } elseif ($method == "GET") {
                    curl_setopt($curlData, CURLOPT_POST, false);
                }
                curl_setopt($curlData, CURLOPT_POSTFIELDS, http_build_query($data));
                curl_setopt($curlData, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curlData, CURLOPT_USERAGENT, 'curl');
                curl_setopt($curlData, CURLOPT_TIMEOUT, 60);
                curl_setopt($curlData, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt ($curlData, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);    
                //cURL + SSL            
                //curl_setopt($curlData, CURLOPT_SSL_VERIFYPEER, false); 
                //curl_setopt($curlData, CURLOPT_SSL_VERIFYHOST, false);
                $result = curl_exec($curlData);
                curl_close($curlData);
                return $result;
            }      
            
        }
        return false;
    }

    /**
     * Creates hidden HTML field
     * 
     * @param string $name  Name of the field. ID parameter will be generated without "[]"
     * @param sting  $value Value of the field 
     *
     * @return string HTML form element
     *
     */
    public function createHiddenField($name, $value)
    {
        $inputId = $name;
        if (substr($name, -2, 2) == "[]") {
            $inputId = substr($name, 0, -2);
        } 
        //$value = addslashes($value);      
        if ($name == "BACK_REF" or $name == "TIMEOUT_URL") {
            $concat = '?';
            if (strpos($value, '?') !== false) {
                $concat = '&';
            }       
            $value .= $concat.'order_ref='.$this->fieldData['ORDER_REF'].'&order_currency='.$this->fieldData['PRICES_CURRENCY'];
        }   
        return "\n<input type='hidden' name='$name' id='$inputId' value='$value' />";
    }
    
    /**
     * Generates a ready-to-insert HTML FORM
     * 
     * @param string $formName          The ID parameter of the form
     * @param string $submitElement     The type of the submit element ('button' or 'link')
     * @param string $submitElementText The lebel for the submit element
     * @param array  $formData          Array of data to be added as hidden fields to the form
     *
     * @return string HTML form
     *
     */
    public function createHtmlForm($formName = 'PayUForm', $submitElement = 'button', $submitElementText = 'Start Payment', $formData = array())
    {   
        $logString = "";
        $form = "\n\n\n<form action='".$this->baseUrl.$this->targetUrl."' method='POST' id='$formName'>";             
        foreach ($formData as $name => $field) {       
            if (is_array($field)) {
                foreach ($field as $subField) {
                    $form .= $this->createHiddenField($name."[]", $subField);
                    $logString .= $name.'='.$subField."\n";
                }
            } elseif (!is_array($field)) {
                $form .= $this->createHiddenField($name, $field);
                $logString .= $name.'='.$field."\n";
            }
        }

        $form .= $this->createHiddenField("SDK_VERSION", $this->sdkVersion());               
        if ($submitElement == "link") {
            $form .= "\n<a href='javascript:document.getElementById(\"$formName\").submit()'>".addslashes($submitElementText)."</a>";
        } elseif ($submitElement == "button") {
            $form .= "\n<button type='submit'>".addslashes($submitElementText)."</button>";
        } elseif ($submitElement == "auto") {
            $form .= "\n<button type='submit'>".addslashes($submitElementText)."</button>";
            $form .= "\n<script language=\"javascript\" type=\"text/javascript\">document.getElementById(\"$formName\").submit();</script>";    
        }        
        $form .= "\n</form>";      

        if ($this->debug) {          
            $this->debugMessage = "<pre>";
            $this->debugMessage .= highlight_string($form, true);
            $this->debugMessage .= "<br>HASH FIELDS<br>";
            $this->debugMessage .= "-----------------------------------------------------------------------------------<br>";
            $this->debugMessage .= print_r($this->hashFields, true);
            $this->debugMessage .= "<br>HASH DATA<br>";
            $this->debugMessage .= "-----------------------------------------------------------------------------------<br>";
            $this->debugMessage .= print_r($this->hashData, true);
            $this->debugMessage .= "<br>HASH STRING<br>";
            $this->debugMessage .= "-----------------------------------------------------------------------------------<br>";
            $this->debugMessage .= $this->hashString."<br>";
            $this->debugMessage .= "<br>HASH CODE<br>";
            $this->debugMessage .= "-----------------------------------------------------------------------------------<br>";
            $this->debugMessage .= $this->hashCode."<br>";
            $this->debugMessage .= "<br>HASH CHECK<br>";
            $this->debugMessage .= "-----------------------------------------------------------------------------------<br>";
            $this->debugMessage .= "<a href='http://hash.online-convert.com/md5-generator'>ONLINE HASH CONVERTER</a><br><br>";
            $this->debugMessage .= "LiveUpdate OBJECT<br>";
            $this->debugMessage .= "-----------------------------------------------------------------------------------<br>";
            $this->debugMessage .= print_r($this, true);
            $this->debugMessage .= "</pre>";         
            $this->report();
        }       
        return $form;
    }

    /**
     * Generates raw data array with HMAC HASH code for custom processing
     * 
     * @param string $hashFieldName Index-name of the generated HASH field in the associative array
     *
     * @return array Data content of form
     *
     */
    public function createPostArray($hashFieldName = "ORDER_HASH")
    {
        if (!$this->prepareFields($hashFieldName)) {
            return false;
        }       
        return $this->formData;
    }  

    /**
     * Sets default value for a field
     * 
     * @param array $sets Array of fields and its parameters
     * 
     * @return void
     * 
     */
    protected function setDefaults($sets)
    {
        foreach ($sets as $set) {
            foreach ($set as $field => $fieldParams) {
                if ($fieldParams['type'] == 'single' && isset($fieldParams['default'])) {
                    $this->fieldData[$field] = $fieldParams['default'];
                }
            }
        }
    }
    
    /**
     * Checks if all required fields are set.
     * Returns true or array of missing fields list
     *
     * @return boolean
     *
     */
    protected function checkRequired()
    {
        $missing = array();
        foreach ($this->validFields as $field => $params) {
            if (isset($params['required']) && $params['required']) {            
                if ($params['type'] == "single") {
                    if (!isset($this->formData[$field])) {
                        $missing[] = $field;
                    }                  
                } elseif ($params['type'] == "product") {                
                    foreach ($this->products as $prod) {                    
                        $paramName = $params['paramName'];
                        if (!isset($prod->$paramName)) {
                            $missing[] = $field;
                        }                      
                    }                  
                }
            }
        }
        $this->missing = $missing;
        return true;
    }
    
    /**
     * Returns a list of missing required fields
     * 
     * @return array $this->missing Missing variables
     */
    public function getMissing()
    {
        return $this->missing;
    }
        
    /**
     * Getter method for fields
     * 
     * @param string $fieldName Name of the field
     * 
     * @return array Data of field
     * 
     */
    public function getField($fieldName)
    {
        return $this->fieldData[$fieldName];
    }
    
    /**
     * Setter method for fields
     * 
     * @param string $fieldName  Name of the field to be set
     * @param string $fieldValue Value of the field to be set
     *
     * @return boolean
     *
     */
    public function setField($fieldName, $fieldValue)
    {
        if (in_array($fieldName, array_keys($this->validFields))) {
            $denied = array("'", "\\", "\"");
            $fieldValue = str_replace($denied, '', $fieldValue);
            $this->fieldData[$fieldName] = $fieldValue;
            return true;
        }
        return false; 
    }
    
    /**
     * Adds product to the $this->product array
     * 
     * @param mixed $product Array description of product or Product object
     *
     * @return void
     *
     */
    public function addProduct($product = array())
    {    
        if (!is_array($product)) {
            $this->error = "Not a valid product!";
            $this->report();          
        } 
        $product = new PayUProduct($product);
        $this->products[] = $product;       
    }
    
    /**
     * Finalizes and prepares fields for sending
     * 
     * @param string $hashName Name of the field containing HMAC HASH code
     *
     * @return boolean
     *
     */
    protected function prepareFields($hashName)
    {
        $this->hashData = array();
        foreach ($this->hashFields as $field) {
            $params = $this->validFields[$field];
            if ($params['type'] == "single") {
                if (isset($this->fieldData[$field])) {
                    $this->hashData[] = $this->fieldData[$field];
                }
            } elseif ($params['type'] == "product") {
                foreach ($this->products as $product) {
                    if (isset($product->{$params["paramName"]})) {
                        $this->hashData[] = $product->{$params["paramName"]};
                    }
                }
            }
        }
        
        foreach ($this->validFields as $field=>$params) {
            if (isset($params["rename"])) {
                $field = $params["rename"];
            }
            if ($params['type'] == "single") {
                if (isset($this->fieldData[$field])) {
                    $this->formData[$field] = $this->fieldData[$field];
                }
            } elseif ($params['type'] == "product") {
                if (!isset($this->formData[$field])) {
                    $this->formData[$field] = array();
                }
                foreach ($this->products as $num=>$product) {
                    if (isset($product->{$params["paramName"]})) {
                        $this->formData[$field][$num] = $product->{$params["paramName"]};
                    }
                }   
            }
        }
        
        if ($this->hashData && $hashName) {
            $this->formData[$hashName] = $this->createHashString($this->hashData);
        }
        
        $this->checkRequired();
        if (count($this->missing) == 0) {
            return true;
        }
        if ($this->debug) {
            echo "REQUIRED FIELDS MISSING\n";
            echo "More info with getMissing()\n";
        }
        return false;        
    }
    
    /**
     * Finds and processes validation response from HTTP response
     * 
     * @param string $resp HTTP response
     * 
     * @return array Data
     * 
     */
    public function processResponse($resp)
    {
        preg_match_all("/<EPAYMENT>(.*?)<\/EPAYMENT>/", $resp, $matches);
        $data = explode("|", $matches[1][0]);
        return $this->nameData($data);
    }
    
    /**
     * Validates HASH code of the response
     * 
     * @param array $resp Array with the response data
     * 
     * @return boolean
     * 
     */
    public function checkResponseHash($resp = array())
    {
        $hash = $resp['ORDER_HASH'];
        array_pop($resp);
        $calculated = $this->createHashString($resp);
        if ($this->debug) {
            echo "\ncalc:".$calculated;
            echo "\nrec: ".$hash."\n";
        }
        if ($hash == $calculated) {
            return true;
        }
        return false;
    }
}

?>
