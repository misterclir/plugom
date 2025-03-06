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

try {
	if(!class_exists('Plugin\ReferralSystem\ReferralSystem')) throw new Exception('Plugin disabled.');
	$ReferralSystem = new Plugin\ReferralSystem\ReferralSystem();
	$ReferralSystem->loadModule('register');
} catch(Exception $ex) {
	message('error', $ex->getMessage());
}