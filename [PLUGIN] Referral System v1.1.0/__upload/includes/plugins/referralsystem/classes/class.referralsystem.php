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

namespace Plugin\ReferralSystem;

class ReferralSystem {
	
	private $_configXml = 'config.xml';
	private $_modulesPath = 'modules';
	private $_sqlPath = 'sql';
	private $_sqlList = array(
		'WEBENGINE_REFERRALSYSTEM'
	);
	private $_cronFile = 'referral_system.php';
	
	private $_requiredLevel;
	private $_requiredMasterLevel;
	private $_requiredResets;
	private $_timeLimitDays;
	private $_rewardCreditConfig;
	private $_rewardCreditAmount;
	
	private $_verificationTimeLimit = 43200;
	
	private $_id;
	private $_referralUsername;
	private $_referralCharacter;
	private $_referredUsername;
	private $_referredCharacter;
	
	private $_usercpmenu = array(
		array(
			'active' => true,
			'type' => 'internal',
			'phrase' => 'referralsystem_title',
			'link' => 'referral/list',
			'icon' => 'usercp_default.png',
			'visibility' => 'user',
			'newtab' => false,
			'order' => 999,
		),
	);
	
	// CONSTRUCTOR
	
	function __construct() {
		
		// load databases
		$this->common = new \common();
		$this->mu = \Connection::Database('MuOnline');
		$this->me = \Connection::Database('Me_MuOnline');
		
		// load configs
		$this->configFilePath = __PATH_REFERRALSYSTEM_ROOT__.$this->_configXml;
		if(!file_exists($this->configFilePath)) throw new \Exception(lang('referralsystem_error_2'));
		$xml = simplexml_load_file($this->configFilePath);
		if(!$xml) throw new \Exception(lang('referralsystem_error_2'));
		
		// set configs
		$this->_requiredLevel = $xml->required_level;
		$this->_requiredMasterLevel = $xml->required_master_level;
		$this->_requiredResets = $xml->required_resets;
		$this->_timeLimitDays = $xml->time_limit_days;
		$this->_rewardCreditConfig = $xml->reward_credit_config;
		$this->_rewardCreditAmount = $xml->reward_credit_amount;
		
		// sql file path
		$this->sqlFilePath = __PATH_REFERRALSYSTEM_ROOT__.$this->_sqlPath.'/';
		
		// check tables
		$this->_checkTables();
		
		// check cron
		$this->_checkCron();
	}
	
	// PUBLIC FUNCTIONS
	
	public function loadModule($module) {
		if(!\Validator::Alpha($module)) throw new \Exception(lang('referralsystem_error_3'));
		if(!$this->_moduleExists($module)) throw new \Exception(lang('referralsystem_error_3'));
		if(!@include_once(__PATH_REFERRALSYSTEM_ROOT__ . $this->_modulesPath . '/' . $module . '.php')) throw new \Exception(lang('referralsystem_error_3'));
	}
	
	public function setId($id) {
		$this->_id = $id;
	}
	
	public function setReferralUsername($username) {
		$this->_referralUsername = $username;
	}
	
	public function setReferralCharacter($character) {
		$this->_referralCharacter = $character;
	}
	
	public function setReferredUsername($username) {
		$this->_referredUsername = $username;
	}
	
	public function setReferredCharacter($character) {
		$this->_referredCharacter = $character;
	}
	
	public function registerReferral() {
		if(!check_value($this->_referralUsername)) return;
		if(!check_value($this->_referralCharacter)) return;
		if(!check_value($this->_referredUsername)) return;
		if($this->_isUsernameAlreadyReferred()) return;
		$data = array(
			$this->_referralUsername,
			$this->_referralCharacter,
			$this->_referredUsername
		);
		$result = $this->me->query("INSERT INTO WEBENGINE_REFERRALSYSTEM (referral_username, referral_character, referred_username, referred_registration_date) VALUES (?, ?, ?, CURRENT_TIMESTAMP)", $data);
		if(!$result) return;
		return true;
	}
	
	public function checkReferrals() {
		$pendingReferralList = $this->_getPendingReferralList();
		if(!is_array($pendingReferralList)) return;
		
		foreach($pendingReferralList as $referralData) {
			
			$this->setId($referralData['id']);
			$this->_updateLastCheck();
			
			// check if account exists
			if(!$this->common->userExists($referralData['referred_username'])) {
				$this->_changeReferralStatus(2);
				continue;
			}
			
			// check time limit
			if($this->_timeLimitDays > 0) {
				if(!check_value($referralData['referred_complete_date'])) {
					$timestampDayLimit = strtotime($referralData['referred_registration_date'])+($this->_timeLimitDays*86400);
					if(time() > $timestampDayLimit) {
						$this->_deleteReferral();
						continue;
					}
				}
			}
			
			// character instance
			$Character = new \Character();
			
			// get account characters
			$accountCharacters = $Character->AccountCharacter($referralData['referred_username']);
			if(!is_array($accountCharacters)) continue;
			
			// characters
			foreach($accountCharacters as $characterName) {
				
				// character data
				$characterData = $Character->CharacterData($characterName);
				if(!is_array($characterData)) continue;
				
				// check level
				if($this->_requiredLevel > 0) {
					if($characterData[_CLMN_CHR_LVL_] < $this->_requiredLevel) continue;
				}
				
				// check master level
				if($this->_requiredMasterLevel > 0) {
					$masterLevelInfo = $Character->getMasterLevelInfo($characterName);
					if(!is_array($masterLevelInfo)) continue;
					if(!array_key_exists(_CLMN_ML_LVL_, $masterLevelInfo)) continue;
					if($masterLevelInfo[_CLMN_ML_LVL_] < $this->_requiredMasterLevel) continue;
				}
				
				//  check resets
				if($this->_requiredResets > 0) {
					if($characterData[_CLMN_CHR_RSTS_] < $this->_requiredResets) continue;
				}
				
				// requirements completed
				$this->setReferredCharacter($characterName);
				$this->_setReferralCompleted();
			}
		}
	}
	
	public function checkUnverifiedReferrals() {
		$unverifiedReferralList = $this->_getUnverifiedReferralList();
		if(!is_array($unverifiedReferralList)) return;
		foreach($unverifiedReferralList as $referralData) {
			// check if account exists
			if(!$this->common->userExists($referralData['referred_username'])) {
				$registrationTimestamp = strtotime($referralData['referred_registration_date']);
				$verificationDateLimit = $registrationTimestamp+$this->_verificationTimeLimit;
				if(time() > $verificationDateLimit) {
					$this->setId($referralData['id']);
					$this->_deleteReferral();
				}
				continue;
			}
			$this->setId($referralData['id']);
			$this->_changeReferralStatus(0);
		}
	}
	
	public function checkPendingRewardReferrals() {
		if($this->_rewardCreditConfig == 0) return;
		if($this->_rewardCreditAmount < 1) return;
		$pendingRewardList = $this->_getPendingRewardCompletedReferrals();
		if(!is_array($pendingRewardList)) return;
		
		foreach($pendingRewardList as $referralData) {
			// send reward
			try {
				$creditSystem = new \CreditSystem();
				$creditSystem->setConfigId($this->_rewardCreditConfig);
				$configSettings = $creditSystem->showConfigs(true);
				switch($configSettings['config_user_col_id']) {
					case 'userid':
						$userId = $this->common->retrieveUserID($referralData['referral_username']);
						if(!check_value($userId)) continue;
						$creditSystem->setIdentifier($userId);
						break;
					case 'username':
						$creditSystem->setIdentifier($referralData['referral_username']);
						break;
					case 'character':
						$creditSystem->setIdentifier($referralData['referral_character']);
						break;
					default:
						continue;
				}
				
				$creditSystem->addCredits($this->_rewardCreditAmount);
			} catch(\Exception $ex) {
				continue;
			}
			
			// update referral status
			$this->setId($referralData['id']);
			$this->_changeReferralStatus(1);
		}
	}
	
	public function getAccountReferrals() {
		if(!check_value($this->_referralUsername)) return;
		$result = $this->me->query_fetch("SELECT * FROM WEBENGINE_REFERRALSYSTEM WHERE referral_username = ?", array($this->_referralUsername));
		if(!is_array($result)) return;
		return $result;
	}
	
	public function getReferralListByStatus($code=0) {
		$result = $this->me->query_fetch("SELECT * FROM WEBENGINE_REFERRALSYSTEM WHERE status = ?", array($code));
		if(!is_array($result)) return;
		return $result;
	}
	
	public function checkPluginUsercpLinks() {
		if(!is_array($this->_usercpmenu)) return;
		$cfg = loadConfig('usercp');
		if(!is_array($cfg)) return;
		foreach($cfg as $usercpMenu) {
			$usercpLinks[] = $usercpMenu['link'];
		}
		foreach($this->_usercpmenu as $pluginMenuLink) {
			if(in_array($pluginMenuLink['link'],$usercpLinks)) continue;
			$cfg[] = $pluginMenuLink;
		}
		usort($cfg, function($a, $b) {
			return $a['order'] - $b['order'];
		});
		$usercpJson = json_encode($cfg, JSON_PRETTY_PRINT);
		$cfgFile = fopen(__PATH_CONFIGS__.'usercp.json', 'w');
		if(!$cfgFile) throw new \Exception('There was a problem opening the usercp file.');
		fwrite($cfgFile, $usercpJson);
		fclose($cfgFile);
	}
	
	// PRIVATE FUNCTIONS
	
	private function _moduleExists($module) {
		if(!check_value($module)) return;
		if(!file_exists(__PATH_REFERRALSYSTEM_ROOT__ . $this->_modulesPath . '/' . $module . '.php')) return;
		return true;
	}
	
	private function _checkTables() {
		if(!is_array($this->_sqlList)) return;
		foreach($this->_sqlList as $tableName) {
			$tableExists = $this->me->query_fetch_single("SELECT * FROM sysobjects WHERE xtype = 'U' AND name = ?", array($tableName));
			if($tableExists) continue;
			if(!$this->_createTable($tableName)) throw new \Exception(lang('referralsystem_error_4', true));
		}
	}
	
	private function _tableFileExists($name) {
		if(!file_exists($this->sqlFilePath.$name.'.txt')) return;
		return true;
	}
	
	private function _createTable($name) {
		if(!in_array($name, $this->_sqlList)) return;
		if(!$this->_tableFileExists($name)) return;
		$query = file_get_contents($this->sqlFilePath.$name.'.txt');
		if(!check_value($query)) return;
		if(!$this->me->query($query)) return;
		return true;
	}
	
	private function _checkCron() {
		$result = $this->me->query_fetch_single("SELECT * FROM ".WEBENGINE_CRON." WHERE cron_file_run = ?", array($this->_cronFile));
		if(is_array($result)) return;
		$this->_createCron();
	}
	
	private function _createCron() {
		if(!file_exists(__PATH_CRON__ . $this->_cronFile)) throw new \Exception(lang('referralsystem_error_5'));
		$cronMd5 = md5_file(__PATH_CRON__ . $this->_cronFile);
		if(!check_value($cronMd5)) throw new \Exception(lang('referralsystem_error_5'));
		$insertData = array(
			'Referral System',
			$this->_cronFile,
			3600,
			1,
			0,
			$cronMd5
		);
		$result = $this->me->query("INSERT INTO ".WEBENGINE_CRON." (cron_name, cron_file_run, cron_run_time, cron_status, cron_protected, cron_file_md5) VALUES (?, ?, ?, ?, ?, ?)", $insertData);
		if(!$result) throw new \Exception(lang('referralsystem_error_5'));
	}
	
	private function _isUsernameAlreadyReferred() {
		if(!check_value($this->_referredUsername)) return;
		$result = $this->me->query_fetch_single("SELECT * FROM WEBENGINE_REFERRALSYSTEM WHERE referred_username = ?", array($this->_referredUsername));
		if(!is_array($result)) return;
		return true;
	}
	
	private function _getPendingReferralList() {
		$result = $this->me->query_fetch("SELECT * FROM WEBENGINE_REFERRALSYSTEM WHERE status = 0");
		if(!is_array($result)) return;
		return $result;
	}
	
	private function _getUnverifiedReferralList() {
		$result = $this->me->query_fetch("SELECT * FROM WEBENGINE_REFERRALSYSTEM WHERE status = 2");
		if(!is_array($result)) return;
		return $result;
	}
	
	private function _changeReferralStatus($code=0) {
		if(!check_value($this->_id)) return;
		$result = $this->me->query("UPDATE WEBENGINE_REFERRALSYSTEM SET status = ? WHERE id = ?", array($code, $this->_id));
		if(!$result) return;
		return true;
	}
	
	private function _deleteReferral() {
		if(!check_value($this->_id)) return;
		$result = $this->me->query("DELETE FROM WEBENGINE_REFERRALSYSTEM WHERE id = ?", array($this->_id));
		if(!$result) return;
		return true;
	}
	
	private function _setReferralCompleted() {
		if(!check_value($this->_id)) return;
		if(!check_value($this->_referredCharacter)) return;
		$result = $this->me->query("UPDATE WEBENGINE_REFERRALSYSTEM SET referred_character = ?, referred_complete_date = CURRENT_TIMESTAMP WHERE id = ?", array($this->_referredCharacter, $this->_id));
		if(!$result) return;
		return true;
	}
	
	private function _updateLastCheck() {
		if(!check_value($this->_id)) return;
		$result = $this->me->query("UPDATE WEBENGINE_REFERRALSYSTEM SET referred_last_check = CURRENT_TIMESTAMP WHERE id = ?", array($this->_id));
		if(!$result) return;
		return true;
	}
	
	private function _getPendingRewardCompletedReferrals() {
		$result = $this->me->query_fetch("SELECT * FROM WEBENGINE_REFERRALSYSTEM WHERE referred_complete_date IS NOT NULL AND status = 0");
		if(!is_array($result)) return;
		return $result;
	}
}