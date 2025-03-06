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

// namespace
namespace Plugin\PaygolDonation;

// plugin root
define('__PATH_PAYGOL_ROOT__', __PATH_PLUGINS__.'paygol/');

// plugin home url
define('__PAYGOL_HOME__', __BASE_URL__.'paygol/');

// admincp
$extra_admincp_sidebar[] = array(
    'Paygol Donations', array(
        array('Settings','paygol&page=settings'),
        array('Packages','paygol&page=packages'),
        array('Logs','paygol&page=logs'),
    )
);

// language phrases
if(file_exists(__PATH_PAYGOL_ROOT__ . 'languages/'.config('language_default', true).'/language.php')) {
	// attempt to load same language as website
	if(!@include_once(__PATH_PAYGOL_ROOT__ . 'languages/'.config('language_default', true).'/language.php')) throw new Exception('Error loading language file (paygol).');
} else {
	// load default language file (en)
	if(!@include_once(__PATH_PAYGOL_ROOT__ . 'languages/en/language.php')) throw new Exception('Error loading language file (paygol).');
}

// load classes
if(!@include_once(__PATH_PAYGOL_ROOT__ . 'classes/class.paygol.php')) throw new Exception(lang('paygol_error_1'));

// check request url
if(check_value($_GET['page']) && check_value($_GET['subpage'])) {
	if(strtolower($_GET['page']) == 'donation' && strtolower($_GET['subpage']) == 'paygol') {
		redirect(1, 'paygol/packages');
	}
}