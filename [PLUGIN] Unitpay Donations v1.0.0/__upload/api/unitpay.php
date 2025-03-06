<?php
/**
 * Unitpay Donations
 * https://webenginecms.org/
 * 
 * @version 1.0.0
 * @author Lautaro Angelico <http://lautaroangelico.com/>
 * @copyright (c) 2013-2020 Lautaro Angelico, All Rights Reserved
 * @build wfd094c4936ed5b6323fd0fcab72ef68
 */

define('access', 'api');

try {
	
	// Load WebEngine CMS
	include('../includes/webengine.php');
	
	//LOGS
	//$data_text = json_encode($_GET, JSON_PRETTY_PRINT);
	//file_put_contents(__DIR__ . "/unitpay.txt", $data_text . "\r\n", FILE_APPEND);
	
	// Unitpay Donations
	$UnitpayDonation = new Plugin\UnitpayDonation\UnitpayDonation();
	
	// Project Data
	$domain     = $UnitpayDonation->getDomain();
	$projectId  = $UnitpayDonation->getProjectId();
	$secretKey  = $UnitpayDonation->getSecretKey();
	$publicId   = $UnitpayDonation->getPublicId();
	$currency   = $UnitpayDonation->getCurrency();
	
	// Unitpay SDK
	$unitPay = new UnitPay($domain, $secretKey);
	
    // Validate request (check ip address, signature and etc)
    $unitPay->checkHandlerRequest();
	
	// Params
    list($method, $params) = array($_GET['method'], $_GET['params']);
	
	// Validation
	if($params['orderCurrency'] != $currency) throw new Exception('Currency did not match.');
	if($params['projectId'] != $projectId) throw new Exception('Project id did not match.');
	
    switch ($method) {
        // Just check order (check server status, check order in DB and etc)
        case 'check':
            print $unitPay->getSuccessHandlerResponse('OK');
            break;
        // Method Pay means that the money received
        case 'pay':
            // Please complete order
			$UnitpayDonation->processPayment($params);
            print $unitPay->getSuccessHandlerResponse('Pay Success');
            break;
        // Method Error means that an error has occurred.
        case 'error':
            // Please log error text.
            print $unitPay->getSuccessHandlerResponse('Error logged');
            break;
        // Method Refund means that the money returned to the client
        case 'refund':
            // Please cancel the order
            print $unitPay->getSuccessHandlerResponse('Order canceled');
            break;
    }
	
} catch (Exception $e) {
	print $unitPay->getErrorHandlerResponse($e->getMessage());
}