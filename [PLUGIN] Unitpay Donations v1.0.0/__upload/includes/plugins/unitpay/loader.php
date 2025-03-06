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

// namespace
namespace Plugin\UnitpayDonation;

// plugin root
define('__PATH_UNITPAY_ROOT__', __PATH_PLUGINS__.'unitpay/');

// plugin home url
define('__UNITPAY_HOME__', __BASE_URL__.'unitpay/');

// admincp
$extra_admincp_sidebar[] = array(
    'Unitpay Donations', array(
        array('Settings','unitpay&page=settings'),
        array('Packages','unitpay&page=packages'),
        array('Logs','unitpay&page=logs'),
    )
);

// language phrases
if(file_exists(__PATH_UNITPAY_ROOT__ . 'languages/'.config('language_default', true).'/language.php')) {
	// attempt to load same language as website
	if(!@include_once(__PATH_UNITPAY_ROOT__ . 'languages/'.config('language_default', true).'/language.php')) throw new Exception('Error loading language file (unitpay).');
} else {
	// load default language file (en)
	if(!@include_once(__PATH_UNITPAY_ROOT__ . 'languages/en/language.php')) throw new Exception('Error loading language file (unitpay).');
}

// load classes
if(!@include_once(__PATH_UNITPAY_ROOT__ . 'classes/UnitPay.php')) throw new Exception(lang('unitpay_error_1'));
if(!@include_once(__PATH_UNITPAY_ROOT__ . 'classes/class.unitpay.php')) throw new Exception(lang('unitpay_error_1'));

// check request url
if(check_value($_GET['page']) && check_value($_GET['subpage'])) {
	if(strtolower($_GET['page']) == 'donation' && strtolower($_GET['subpage']) == 'unitpay') {
		redirect(1, 'unitpay/packages');
	}
}