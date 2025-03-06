<?php
/**
 * Referral System
 * https://webenginecms.org/
 * 
 * @version 1.0.0
 * @author Lautaro Angelico <http://lautaroangelico.com/>
 * @copyright (c) 2013-2019 Lautaro Angelico, All Rights Reserved
 * @build wfd094c4936ed5b6323fd0fcab72ef68
 */

// namespace
namespace Plugin\ReferralSystem;

// plugin root
define('__PATH_REFERRALSYSTEM_ROOT__', __PATH_PLUGINS__.'referralsystem/');

// plugin home url
define('__REFERRALSYSTEM_HOME__', __BASE_URL__.'referral/');

// admincp
$extra_admincp_sidebar[] = array(
    'Referral System', array(
        array('Settings','referralsystem&page=settings'),
        array('Referrals','referralsystem&page=logs'),
    )
);

// language phrases
if(file_exists(__PATH_REFERRALSYSTEM_ROOT__ . 'languages/'.config('language_default', true).'/language.php')) {
	// attempt to load same language as website
	if(!@include_once(__PATH_REFERRALSYSTEM_ROOT__ . 'languages/'.config('language_default', true).'/language.php')) throw new Exception('Error loading language file (referralsystem).');
} else {
	// load default language file (en)
	if(!@include_once(__PATH_REFERRALSYSTEM_ROOT__ . 'languages/en/language.php')) throw new Exception('Error loading language file (referralsystem).');
}

// load classes
if(!@include_once(__PATH_REFERRALSYSTEM_ROOT__ . 'classes/class.referralsystem.php')) throw new Exception(lang('referralsystem_error_1'));

// check request url
if(check_value($_GET['page']) && check_value($_COOKIE['referral'])) {
	if(strtolower($_GET['page']) == 'register') {
		redirect(1, 'referral/register');
	}
}