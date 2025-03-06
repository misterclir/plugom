<?php
/**
 * Paygol Donations
 * https://webenginecms.org/
 * 
 * @version 1.0.0
 * @author Lautaro Angelico <http://lautaroangelico.com/>
 * @copyright (c) 2013-2020 Lautaro Angelico, All Rights Reserved
 * @build wfd094c4936ed5b6323fd0fcab72ef68
 */

define('access', 'api');

try {
	
	include('../includes/webengine.php');
	if(!@include_once(__PATH_PAYGOL_ROOT__ . 'classes/vendor/autoload.php')) throw new Exception(lang('paygol_error_1'));
	
	$PaygolDonation = new \Plugin\PaygolDonation\PaygolDonation();
	
	$service_id = $PaygolDonation->getServiceId();
	$shared_secret = $PaygolDonation->getSharedSecret();
	$ipn = new Paygol\Notification($service_id, $shared_secret);
	
	if($PaygolDonation->validation == true) {
		$ipn->validate();
	}
	
	$PaygolDonation->processPayment($_GET);
    $ipn->sendResponse(['OK'], 200);
	
} catch (Exception $e) {
    $ipn->sendResponse(['error' => $e->getMessage()], 400);
}