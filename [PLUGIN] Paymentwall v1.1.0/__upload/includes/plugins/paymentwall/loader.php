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

// namespace
namespace Plugin\PaymentWallGateway;

// plugin root
define('__PATH_PAYMENTWALL_ROOT__', __PATH_PLUGINS__.'paymentwall/');

// plugin home url
define('__PAYMENTWALL_HOME__', __BASE_URL__.'paymentwall/');

// admincp
$extra_admincp_sidebar[] = array(
    'PaymentWall', array(
        array('Settings','paymentwall&page=settings'),
        array('Donation Logs','paymentwall&page=logs'),
    )
);

// language phrases
if(file_exists(__PATH_PAYMENTWALL_ROOT__ . 'languages/'.config('language_default', true).'/language.php')) {
	// attempt to load same language as website
	if(!@include_once(__PATH_PAYMENTWALL_ROOT__ . 'languages/'.config('language_default', true).'/language.php')) throw new Exception('Error loading language file (paymentwall).');
} else {
	// load default language file (en)
	if(!@include_once(__PATH_PAYMENTWALL_ROOT__ . 'languages/en/language.php')) throw new Exception('Error loading language file (paymentwall).');
}

// load classes
if(!@include_once(__PATH_PAYMENTWALL_ROOT__ . 'classes/paymentwall.php')) throw new Exception(lang('paymentwall_error_1'));
if(!@include_once(__PATH_PAYMENTWALL_ROOT__ . 'classes/class.paymentwall.php')) throw new Exception(lang('paymentwall_error_1'));