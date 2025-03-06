<?php
/**
 * Referral System
 * https://webenginecms.org/
 * 
 * @version 1.1.0
 * @author Lautaro Angelico <http://lautaroangelico.com/>
 * @copyright (c) 2013-2021 Lautaro Angelico, All Rights Reserved
 * @build wfd094c4936ed5b6323fd0fcab72ef68
 */

if(isLoggedIn()) redirect();

echo '<div class="page-title"><span>'.lang('referralsystem_title').'</span></div>';

try {
	
	// check referral player
	if(!check_value($_GET['player'])) redirect();
	
	// clear referral cookie
	unset($_COOKIE['referral']);
	
	// set referral cookie
	setcookie('referral', $_GET['player'], time()+86400, "/");
	
	// redirect home
	redirect(1, 'referral/register');
	
} catch(Exception $ex) {
	message('error', $ex->getMessage());
}