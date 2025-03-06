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

if(isLoggedIn()) redirect();
echo '<div class="page-title"><span>'.lang('module_titles_txt_1',true).'</span></div>';

try {
	
	if(!check_value($_COOKIE['referral'])) redirect(1, 'register');
	
	$registerConfig = loadConfigurations('register');
	if(!is_array($registerConfig)) throw new Exception(lang('referralsystem_error_6'));
	if(!$registerConfig['active']) throw new Exception(lang('error_17',true));
	
	// Register Process
	if(check_value($_POST['webengineRegister_submit'])) {
		try {
			$Account = new Account();
			
			if($registerConfig['register_enable_recaptcha']) {
				if(!@include_once(__PATH_CLASSES__ . 'recaptcha/autoload.php')) throw new Exception(lang('error_60'));
				$recaptcha = new \ReCaptcha\ReCaptcha($registerConfig['register_recaptcha_secret_key']);
				
				$resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
				if(!$resp->isSuccess()) {
					# recaptcha failed
					$errors = $resp->getErrorCodes();
					throw new Exception(lang('error_18',true));
				}
			}
			
			$Account->registerAccount($_POST['webengineRegister_user'], $_POST['webengineRegister_pwd'], $_POST['webengineRegister_pwdc'], $_POST['webengineRegister_email']);
			
			// referral system
			$Character = new Character();
			$characterData = $Character->CharacterData($_COOKIE['referral']);
			if(is_array($characterData)) {
				$ReferralSystem = new \Plugin\ReferralSystem\ReferralSystem();
				$ReferralSystem->setReferralUsername($characterData[_CLMN_CHR_ACCID_]);
				$ReferralSystem->setReferralCharacter($characterData[_CLMN_CHR_NAME_]);
				$ReferralSystem->setReferredUsername($_POST['webengineRegister_user']);
				$ReferralSystem->registerReferral();
				unset($_COOKIE['referral']);
				setcookie('referral', '', time()-3600, "/");
			}
			
		} catch (Exception $ex) {
			message('error', $ex->getMessage());
		}
	}
	
	echo '<div class="col-xs-8 col-xs-offset-2" style="margin-top:30px;">';
		echo '<form class="form-horizontal" action="" method="post">';
			echo '<div class="form-group">';
				echo '<label for="webengineRegistration1" class="col-sm-4 control-label">'.lang('register_txt_1',true).'</label>';
				echo '<div class="col-sm-8">';
					echo '<input type="text" class="form-control" id="webengineRegistration1" name="webengineRegister_user" required>';
					echo '<span id="helpBlock" class="help-block">'.lang('register_txt_6',true).'</span>';
				echo '</div>';
			echo '</div>';
			echo '<div class="form-group">';
				echo '<label for="webengineRegistration2" class="col-sm-4 control-label">'.lang('register_txt_2',true).'</label>';
				echo '<div class="col-sm-8">';
					echo '<input type="password" class="form-control" id="webengineRegistration2" name="webengineRegister_pwd" required>';
					echo '<span id="helpBlock" class="help-block">'.lang('register_txt_7',true).'</span>';
				echo '</div>';
			echo '</div>';
			echo '<div class="form-group">';
				echo '<label for="webengineRegistration3" class="col-sm-4 control-label">'.lang('register_txt_3',true).'</label>';
				echo '<div class="col-sm-8">';
					echo '<input type="password" class="form-control" id="webengineRegistration3" name="webengineRegister_pwdc" required>';
					echo '<span id="helpBlock" class="help-block">'.lang('register_txt_8',true).'</span>';
				echo '</div>';
			echo '</div>';
			echo '<div class="form-group">';
				echo '<label for="webengineRegistration4" class="col-sm-4 control-label">'.lang('register_txt_4',true).'</label>';
				echo '<div class="col-sm-8">';
					echo '<input type="text" class="form-control" id="webengineRegistration4" name="webengineRegister_email" required>';
					echo '<span id="helpBlock" class="help-block">'.lang('register_txt_9',true).'</span>';
				echo '</div>';
			echo '</div>';
			
			if($registerConfig['register_enable_recaptcha']) {
				# recaptcha v2
				echo '<div class="form-group">';
					echo '<div class="col-sm-offset-4 col-sm-8">';
						echo '<div class="g-recaptcha" data-sitekey="'.$registerConfig['register_recaptcha_site_key'].'"></div>';
					echo '</div>';
				echo '</div>';
				echo '<script src=\'https://www.google.com/recaptcha/api.js\'></script>';
			}
			
			echo '<div class="form-group">';
				echo '<div class="col-sm-offset-4 col-sm-8">';
					echo langf('register_txt_10', array(__BASE_URL__.'tos'));
				echo '</div>';
			echo '</div>';
			echo '<div class="form-group">';
				echo '<div class="col-sm-offset-4 col-sm-8">';
					echo '<button type="submit" name="webengineRegister_submit" value="submit" class="btn btn-primary">'.lang('register_txt_5',true).'</button>';
				echo '</div>';
			echo '</div>';
		echo '</form>';
	echo '</div>';
	
} catch(Exception $ex) {
	message('error', $ex->getMessage());
}