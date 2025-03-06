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

if(!isLoggedIn()) redirect(1,'login');

echo '<div class="page-title"><span>'.lang('unitpay_title',true).'</span></div>';
echo '<div class="text-center" style="margin-bottom:20px;"><img src="'.__PATH_TEMPLATE_IMG__.'unitpay_logo.png" width="60%" height="auto" /></div>';

try {
	
	if(!check_value($_GET['id'])) throw new Exception(lang('unitpay_error_5'));
	
	$packagesList = $UnitpayDonation->getPackageList();
	if(!is_array($packagesList)) throw new Exception(lang('unitpay_error_17'));
	if(!array_key_exists($_GET['id'], $packagesList)) throw new Exception('The provided package id is not valid.');
	
	$packageInfo = $packagesList[$_GET['id']];
	if(!is_array($packageInfo)) throw new Exception(lang('unitpay_error_5'));
	
	// Project Data
	$domain     = $UnitpayDonation->getDomain();
	$projectId  = $UnitpayDonation->getProjectId();
	$secretKey  = $UnitpayDonation->getSecretKey();
	$publicId   = $UnitpayDonation->getPublicId();

	// Item Info
	$itemName = $packageInfo['title'];

	// Order Data
	$orderId        = $_GET['id'] . '-' . $_SESSION['userid'];
	$orderSum       = $packageInfo['cost'];
	$orderDesc      = 'Payment for item "'.$itemName.'"';
	$orderCurrency  = $UnitpayDonation->getCurrency();

	$unitPay = new UnitPay($domain, $secretKey);

	$redirectUrl = $unitPay->form(
		$publicId,
		$orderSum,
		$orderId,
		$orderDesc,
		$orderCurrency
	);

	header("Location: " . $redirectUrl);
	
} catch(Exception $ex) {
	message('error', $ex->getMessage());
}