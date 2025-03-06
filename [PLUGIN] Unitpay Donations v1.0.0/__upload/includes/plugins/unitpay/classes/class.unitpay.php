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

namespace Plugin\UnitpayDonation;

class UnitpayDonation {
	
	private $_configFile = 'config.json';
	private $_modulesPath = 'modules';
	private $_packagesFile = 'packages.json';
	private $_sqlTable = WE_PREFIX . 'WEBENGINE_UNITPAY_LOGS';
	private $_sqlPath = 'sql';
	private $_sqlFileName = 'WEBENGINE_UNITPAY_LOGS.txt';
	
	private $_active;
	private $_creditConfig;
	private $_currency;
	private $_currencySymbol;
	private $_projectId;
	private $_secretKey;
	private $_publicId;
	private $_domain;
	
	private $_packageId;
	private $_packageTitle;
	private $_packageCost;
	private $_packageCredits;
	
	// CONSTRUCTOR
	
	function __construct() {
				
		$this->configFilePath = __PATH_UNITPAY_ROOT__ . $this->_configFile;
		if(!file_exists($this->configFilePath)) throw new \Exception(lang('unitpay_error_2'));
		$cfgFile = file_get_contents(__PATH_UNITPAY_ROOT__ . $this->_configFile);
		if(!$cfgFile) throw new \Exception(lang('unitpay_error_2'));
		$cfg = json_decode($cfgFile, true);
		if(!is_array($cfg)) throw new \Exception(lang('unitpay_error_2'));
		
		$this->_active = $cfg['active'];
		$this->_creditConfig = $cfg['credit_config'];
		$this->_currency = $cfg['currency'];
		$this->_currencySymbol = $cfg['currency_symbol'];
		$this->_projectId = $cfg['project_id'];
		$this->_secretKey = $cfg['secret_key'];
		$this->_publicId = $cfg['public_id'];
		$this->_domain = $cfg['domain'];
		
		// sql file path
		$this->sqlFilePath = __PATH_UNITPAY_ROOT__.$this->_sqlPath.'/';
	}
	
	// PUBLIC FUNCTIONS
	
	public function loadModule($module) {
		if($this->_active != true) throw new \Exception(lang('unitpay_error_20'));
		if(!\Validator::Alpha($module)) throw new \Exception(lang('unitpay_error_3'));
		if(!$this->_moduleExists($module)) throw new \Exception(lang('unitpay_error_3'));
		$UnitpayDonation = $this;
		if(!@include_once(__PATH_UNITPAY_ROOT__ . $this->_modulesPath . '/' . $module . '.php')) throw new \Exception(lang('unitpay_error_3'));
	}
	
	public function setPackageId($id) {
		if(!\Validator::Chars($id, array('a-z', '0-9'))) throw new \Exception(lang('unitpay_error_5'));
		$this->_packageId = $id;
	}
	
	public function setPackageTitle($title) {
		if(!\Validator::Length($title, 100, 1)) throw new \Exception(lang('unitpay_error_6'));
		$this->_packageTitle = utf8_encode($title);
	}
	
	public function setPackageCost($cost) {
		if(!is_numeric($cost)) throw new \Exception(lang('unitpay_error_7'));
		if($cost < 1) throw new \Exception(lang('unitpay_error_7'));
		$this->_packageCost = $cost;
	}
	
	public function setPackageCredits($credits) {
		if(!\Validator::UnsignedNumber($credits)) throw new \Exception(lang('unitpay_error_8'));
		if($credits < 1) throw new \Exception(lang('unitpay_error_8'));
		$this->_packageCredits = $credits;
	}
	
	public function addNewPackage() {
		if(!check_value($this->_packageId)) throw new \Exception(lang('unitpay_error_9'));
		if(!check_value($this->_packageTitle)) throw new \Exception(lang('unitpay_error_10'));
		if(!check_value($this->_packageCost)) throw new \Exception(lang('unitpay_error_11'));
		if(!check_value($this->_packageCredits)) throw new \Exception(lang('unitpay_error_12'));
		
		$packages = $this->getPackageList();
		if(!is_array($packages)) {
			$packages = array();
		}
		
		$packageExists = $this->getPackageInformation();
		if(is_array($packageExists)) throw new \Exception(lang('unitpay_error_13'));
		
		$packages[$this->_packageId] = array(
			'title' => $this->_packageTitle,
			'cost' => $this->_packageCost,
			'credits' => $this->_packageCredits
		);
		
		$save = $this->_updatePackagesFile($packages);
		if(!$save) throw new \Exception(lang('unitpay_error_14'));
	}
	
	public function deletePackage() {
		if(!check_value($this->_packageId)) throw new \Exception(lang('unitpay_error_15'));
		
		$packageExists = $this->getPackageInformation();		
		if(!is_array($packageExists)) throw new \Exception(lang('unitpay_error_16'));
		
		$packages = $this->getPackageList();
		unset($packages[$this->_packageId]);
		
		$save = $this->_updatePackagesFile($packages);
		if(!$save) throw new \Exception(lang('unitpay_error_14'));
	}
	
	public function getPackageInformation() {
		if(!check_value($this->_packageId)) return;
		$packages = $this->getPackageList();
		if(!is_array($packages)) return;
		if(!array_key_exists($this->_packageId, $packages)) return;
		return $packages[$this->_packageId];
	}
	
	public function getPackageList() {
		$packages = file_get_contents(__PATH_UNITPAY_ROOT__ . $this->_packagesFile);
		if(!$packages) return;
		$packagesToArray = json_decode($packages, true);
		if(!is_array($packagesToArray)) return;
		if(count($packagesToArray) < 1) return;
		return $packagesToArray;
	}
	
	public function getCurrency() {
		return $this->_currency;
	}
	
	public function getCurrencySymbol() {
		return utf8_decode($this->_currencySymbol);
	}
	
	public function getCreditsTitle() {
		$creditSystem = new \CreditSystem();
		$creditSystem->setConfigId($this->_creditConfig);
		$configSettings = $creditSystem->showConfigs(true);
		if(!is_array($configSettings)) return;
		return $configSettings['config_title'];
	}
	
	public function getProjectId() {
		return $this->_projectId;
	}
	
	public function getSecretKey() {
		return $this->_secretKey;
	}
	
	public function getPublicId() {
		return $this->_publicId;
	}
	
	public function getDomain() {
		return $this->_domain;
	}
	
	public function processPayment($data=array()) {
		
		$db = $this->_getAccountsDatabaseObject();
		
		$duplicateTransaction = $db->query_fetch_single("SELECT * FROM ".$this->_sqlTable." WHERE transaction_id = ?", array($data['unitpayId']));
		if(is_array($duplicateTransaction)) throw new \Exception('Duplicate transaction detected.');
		
		$custom = explode('-', $data['account']);
		if(!is_array($custom)) throw new \Exception('Order data incomplete.');
		
		$package = $custom[0];
		$userid = $custom[1];
		
		$this->setPackageId($package);
		$packageInfo = $this->getPackageInformation();
		if(!is_array($packageInfo)) throw new \Exception('Package id is not valid.');
		if($data['orderSum'] != $packageInfo['cost']) throw new \Exception('Package cost is not valid.');
		$credits = $packageInfo['credits'];
		
		$Account = new \Account();
		$accountData = $Account->accountInformation($userid);
		if(!is_array($accountData)) throw new \Exception('Could not load account information');
		
		$creditSystem = new \CreditSystem();
		$creditSystem->setConfigId($this->_creditConfig);
		$configSettings = $creditSystem->showConfigs(true);
		switch($configSettings['config_user_col_id']) {
			case 'userid':
				$creditSystem->setIdentifier($userid);
				break;
			case 'username':
				$creditSystem->setIdentifier($accountData[_CLMN_USERNM_]);
				break;
			case 'email':
				$creditSystem->setIdentifier($accountData[_CLMN_EMAIL_]);
				break;
			default:
				throw new \Exception("Invalid identifier (credit system).");
		}
		$creditSystem->addCredits(abs($credits));
		
		$insertData = array(
			$data['unitpayId'],
			$data['operator'],
			$data['orderCurrency'],
			$data['orderSum'],
			$data['paymentType'],
			$data['signature'],
			$credits,
			$package,
			$accountData[_CLMN_USERNM_]
		);
		
		$query = "INSERT INTO ".$this->_sqlTable." (transaction_id,operator,order_currency,order_sum,payment_type,signature,credits,package,username,timestamp) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";
		
		$saveLog = $db->query($query, $insertData);
		if(!$saveLog) throw new \Exception($db->error);
	}
	
	public function checkUnitpayTable() {
		$this->_checkTable();
	}
	
	public function getLogs($limit=1000) {
		$db = $this->_getAccountsDatabaseObject();
		$result = $db->query_fetch("SELECT TOP ".$limit." * FROM ".$this->_sqlTable." ORDER BY timestamp DESC");
		if(!is_array($result)) return;
		return $result;
	}
	
	// PRIVATE FUNCTIONS
	
	private function _moduleExists($module) {
		if(!check_value($module)) return;
		if(!file_exists(__PATH_UNITPAY_ROOT__ . $this->_modulesPath . '/' . $module . '.php')) return;
		return true;
	}
	
	private function _checkTable() {
		$db = $this->_getAccountsDatabaseObject();
		$tableExists = $db->query_fetch_single("SELECT * FROM sysobjects WHERE xtype = 'U' AND name = ?", array($this->_sqlTable));
		if($tableExists) return;
		if(!file_exists($this->sqlFilePath.$this->_sqlFileName)) throw new \Exception(lang('unitpay_error_4', true));
		$query = file_get_contents($this->sqlFilePath.$this->_sqlFileName);
		if(!check_value($query)) throw new \Exception(lang('unitpay_error_4', true));
		$queryFinal = str_replace('{TABLE_NAME}', $this->_sqlTable, $query);
		if(!$queryFinal) throw new \Exception(lang('unitpay_error_4', true));
		if(!$db->query($queryFinal)) throw new \Exception(lang('unitpay_error_4', true));
		return true;
	}
	
	private function _updatePackagesFile($data) {
		$file = __PATH_UNITPAY_ROOT__ . $this->_packagesFile;
		$fp = fopen($file, 'w');
		if(!fwrite($fp, json_encode($data, JSON_PRETTY_PRINT))) return;
		fclose($fp);
		return true;
	}
	
	private function _getAccountsDatabaseObject() {
		return \Connection::Database('Me_MuOnline');
	}
	
}