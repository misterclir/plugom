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

if(!isLoggedIn()) redirect(1, 'login');

echo '<div class="page-title"><span>'.lang('referralsystem_title').'</span></div>';

try {
	
	$ReferralSystem = new \Plugin\ReferralSystem\ReferralSystem();
	$ReferralSystem->setReferralUsername($_SESSION['username']);
	
	$Character = new Character();
	$AccountCharacters = $Character->AccountCharacter($_SESSION['username']);
	if(!is_array($AccountCharacters)) throw new Exception(lang('error_46',true));
	
	echo '<h3>'.lang('referralsystem_txt_1').'</h3>';
	echo '<input type="text" value="'.__REFERRALSYSTEM_HOME__.'invite/player/'.$AccountCharacters[array_rand($AccountCharacters)].'" class="form-control" style="margin-bottom:20px;" readonly/>';
	
	echo '<h3>'.lang('referralsystem_txt_6').'</h3>';
	$referrals = $ReferralSystem->getAccountReferrals();
	if(is_array($referrals)) {
		
		echo '<table class="table">';
			echo '<thead>';
				echo '<tr>';
					echo '<th>Registration Date</th>';
					echo '<th>Referred Player</th>';
					echo '<th>Complete Date</th>';
					echo '<th>Status</th>';
				echo '</tr>';
			echo '</thead>';
			echo '<tbody>';
			foreach($referrals as $referralData) {
				$referralStatus = $referralData['status'] == 1 ? lang('referralsystem_txt_4') : lang('referralsystem_txt_3');
				$referredPlayer = check_value($referralData['referred_character']) ? playerProfile($referralData['referred_character']) : lang('referralsystem_txt_5');
				echo '<tr>';
					echo '<td>'.$referralData['referred_registration_date'].'</td>';
					echo '<td>'.$referredPlayer.'</td>';
					echo '<td>'.$referralData['referred_complete_date'].'</td>';
					echo '<td>'.$referralStatus.'</td>';
				echo '</tr>';
			}
			echo '</tbody>';
		echo '</table>';
		
	} else {
		message('warning', lang('referralsystem_txt_2'));
	}
	
} catch(Exception $ex) {
	message('error', $ex->getMessage());
}