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

try {
	if(!class_exists('Plugin\UnitpayDonation\UnitpayDonation')) throw new Exception('Plugin disabled.');
	$UnitpayDonation = new Plugin\UnitpayDonation\UnitpayDonation();
	$UnitpayDonation->loadModule('packages');
} catch(Exception $ex) {
	message('error', $ex->getMessage());
}