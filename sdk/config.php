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
 

$config = array(   
    'HUF_MERCHANT' => "P096501", //merchant account ID (HUF)
    'HUF_SECRET_KEY' => "", //secret key for account ID (HUF)   	
    'EUR_MERCHANT' => "", //merchant account ID (EUR)
    'EUR_SECRET_KEY' => "", //secret key for account ID (EUR)
    'USD_MERCHANT' => "", //merchant account ID (USD)
    'USD_SECRET_KEY' => "", //secret key for account ID (USD)
    'METHOD' => "CCVISAMC",                                             //payment method     empty -> select payment method on PayU payment page OR [ CCVISAMC, WIRE ]
    'ORDER_DATE' => @date("Y-m-d H:i:s"),                                //date of transaction
    'LOGGER' => true,                                                   //transaction log
    'LOG_PATH' => 'log',                                                //path of log file
    'BACK_REF' => 'http://' . $_SERVER['HTTP_HOST'] . '/backref.php',        //url of payu payment backref page
    'TIMEOUT_URL' => 'http://' . $_SERVER['HTTP_HOST'] . '/timeout.php', //url of payu payment timeout page
    'IRN_BACK_URL' => 'http://' . $_SERVER['HTTP_HOST'] . '/irn.php',        //url of payu payment irn page
    'IDN_BACK_URL' => 'http://' . $_SERVER['HTTP_HOST'] . '/idn.php',        //url of payu payment idn page
    'CURL' => true,
    'ORDER_TIMEOUT' => 300,
    'LANGUAGE' => 'HU',
    'GET_DATA' => $_GET,
    'POST_DATA' => $_POST,
    'SERVER_DATA' => $_SERVER,
	'MIGRATION' => true,
	'SANDBOX' => false,
);

