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

try {
	if(!class_exists('Plugin\PaygolDonation\PaygolDonation')) throw new Exception('Plugin disabled.');
	$PaygolDonation = new Plugin\PaygolDonation\PaygolDonation();
	$PaygolDonation->loadModule('order');
} catch(Exception $ex) {
	message('error', $ex->getMessage());
}