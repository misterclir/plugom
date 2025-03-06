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

// File Name
$file_name = basename(__FILE__);

// Run Cron
$ReferralSystem = new \Plugin\ReferralSystem\ReferralSystem();
$ReferralSystem->checkUnverifiedReferrals();
$ReferralSystem->checkReferrals();
$ReferralSystem->checkPendingRewardReferrals();

// UPDATE CRON
updateCronLastRun($file_name);