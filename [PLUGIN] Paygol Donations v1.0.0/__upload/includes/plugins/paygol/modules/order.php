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

use \Paygol\Webcheckout;
use \Paygol\Models\Payer;
use \Paygol\Models\RedirectUrls;
use \Paygol\Exceptions\InvalidParameterException;

if(!isLoggedIn()) redirect(1,'login');

echo '<div class="page-title"><span>'.lang('paygol_title',true).'</span></div>';
echo '<div class="text-center" style="margin-bottom:20px;"><img src="'.__PATH_TEMPLATE_IMG__.'paygol_logo.png" width="60%" height="auto" /></div>';

try {
	
	if(!check_value($_GET['id'])) throw new Exception(lang('paygol_error_5'));
	
	$packagesList = $PaygolDonation->getPackageList();
	if(!is_array($packagesList)) throw new Exception(lang('paygol_error_17'));
	if(!array_key_exists($_GET['id'], $packagesList)) throw new Exception('The provided package id is not valid.');
	
	$packageInfo = $packagesList[$_GET['id']];
	if(!is_array($packageInfo)) throw new Exception(lang('paygol_error_5'));
	
	$userCountry = getCountryCodeFromIp($_SERVER['REMOTE_ADDR']);
	if(!check_value($userCountry)) throw new Exception(lang('paygol_error_18'));	
	
	$pg = new Webcheckout($PaygolDonation->getServiceId(), $PaygolDonation->getSharedSecret());
	
	$redirectUrls = new RedirectUrls();
    $redirectUrls->setRedirects(
        __PAYGOL_HOME__ . 'packages/success/1', 
        __BASE_URL__
    );

    $pg->setRedirects($redirectUrls);
	$pg->setCountry($userCountry);
    $pg->setPrice($packageInfo['cost'], $PaygolDonation->getCurrency());
    $pg->setName($packageInfo['title']);
    $pg->setCustom($_GET['id'].'-'.$_SESSION['userid']);
	$payment = $pg->createPayment();
	
} catch(InvalidParameterException $ex) {
	message('error', $ex->getMessage());
} catch(Exception $ex) {
	message('error', $ex->getMessage());
}