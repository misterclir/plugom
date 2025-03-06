<?php
/**
 * PaymentWall
 * https://webenginecms.org/
 * 
 * @version 1.0.0
 * @author Lautaro Angelico <http://lautaroangelico.com/>
 * @copyright (c) 2013-2019 Lautaro Angelico, All Rights Reserved
 * @build wfd094c4936ed5b6323fd0fcab72ef68
 */

// access
define('access', 'api');

try {
	
	// WebEngine CMS
	if(!@include_once(rtrim(str_replace('\\','/', dirname(__DIR__)), '/') . '/includes/webengine.php')) throw new Exception('Could not load WebEngine CMS.');
	
	$PaymentWallGateway = new Plugin\PaymentWallGateway\PaymentWallGateway();
	
	Paymentwall_Base::setApiType(Paymentwall_Base::API_VC);
	Paymentwall_Base::setAppKey($PaymentWallGateway->getProjectKey());
	Paymentwall_Base::setSecretKey($PaymentWallGateway->getSecretKey());
	
	
	$pingback = new Paymentwall_Pingback($_GET, $_SERVER['REMOTE_ADDR']);
	
	if($pingback->validate()) {
		
		if ($pingback->isDeliverable()) {
			// deliver the virtual currency
			$PaymentWallGateway->processPayment($_GET);
			
		} else if ($pingback->isCancelable()) {
			// withdraw the virtual currency
			$PaymentWallGateway->processCancelation($_GET);
		}
		
		echo 'OK';
		
	} else {
		throw new Exception($pingback->getErrorSummary());
	}
	
} catch(Exception $ex) {
	die($ex->getMessage());
}