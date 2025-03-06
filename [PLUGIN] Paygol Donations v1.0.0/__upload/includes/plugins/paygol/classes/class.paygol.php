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

namespace Plugin\PaygolDonation;

class PaygolDonation {
	
	private $_configFile = 'config.json';
	private $_modulesPath = 'modules';
	private $_packagesFile = 'packages.json';
	private $_sqlTable = WE_PREFIX . 'WEBENGINE_PAYGOL_LOGS';
	private $_sqlPath = 'sql';
	private $_sqlFileName = 'WEBENGINE_PAYGOL_LOGS.txt';
	
	private $_active;
	private $_creditConfig;
	private $_currency;
	private $_currencySymbol;
	private $_serviceId;
	private $_sharedSecret;
	
	private $_packageId;
	private $_packageTitle;
	private $_packageCost;
	private $_packageCredits;
	
	public $validation = false;
	public $filesystemLogs = false;
	
	// CONSTRUCTOR
	
	function __construct() {
				
		$this->configFilePath = __PATH_PAYGOL_ROOT__ . $this->_configFile;
		if(!file_exists($this->configFilePath)) throw new \Exception(lang('paygol_error_2'));
		$cfgFile = file_get_contents(__PATH_PAYGOL_ROOT__ . $this->_configFile);
		if(!$cfgFile) throw new \Exception(lang('paygol_error_2'));
		$cfg = json_decode($cfgFile, true);
		if(!is_array($cfg)) throw new \Exception(lang('paygol_error_2'));
		
		$this->_active = $cfg['active'];
		$this->_creditConfig = $cfg['credit_config'];
		$this->_currency = $cfg['currency'];
		$this->_currencySymbol = $cfg['currency_symbol'];
		$this->_serviceId = $cfg['service_id'];
		$this->_sharedSecret = $cfg['shared_secret'];
		
		// sql file path
		$this->sqlFilePath = __PATH_PAYGOL_ROOT__.$this->_sqlPath.'/';
	}
	
	// PUBLIC FUNCTIONS
	
	public function loadModule($module) {
		if($this->_active != true) throw new \Exception(lang('paygol_error_20'));
		if(!\Validator::Alpha($module)) throw new \Exception(lang('paygol_error_3'));
		if(!$this->_moduleExists($module)) throw new \Exception(lang('paygol_error_3'));
		$PaygolDonation = $this;
		if(!@include_once(__PATH_PAYGOL_ROOT__ . 'classes/vendor/autoload.php')) throw new \Exception(lang('paygol_error_1'));
		if(!@include_once(__PATH_PAYGOL_ROOT__ . $this->_modulesPath . '/' . $module . '.php')) throw new \Exception(lang('paygol_error_3'));
	}
	
	public function setPackageId($id) {
		if(!\Validator::Chars($id, array('a-z', '0-9'))) throw new \Exception(lang('paygol_error_5'));
		$this->_packageId = $id;
	}
	
	public function setPackageTitle($title) {
		if(!\Validator::Length($title, 100, 1)) throw new \Exception(lang('paygol_error_6'));
		$this->_packageTitle = utf8_encode($title);
	}
	
	public function setPackageCost($cost) {
		if(!is_numeric($cost)) throw new \Exception(lang('paygol_error_7'));
		if($cost < 1) throw new \Exception(lang('paygol_error_7'));
		$this->_packageCost = $cost;
	}
	
	public function setPackageCredits($credits) {
		if(!\Validator::UnsignedNumber($credits)) throw new \Exception(lang('paygol_error_8'));
		if($credits < 1) throw new \Exception(lang('paygol_error_8'));
		$this->_packageCredits = $credits;
	}
	
	public function addNewPackage() {
		if(!check_value($this->_packageId)) throw new \Exception(lang('paygol_error_9'));
		if(!check_value($this->_packageTitle)) throw new \Exception(lang('paygol_error_10'));
		if(!check_value($this->_packageCost)) throw new \Exception(lang('paygol_error_11'));
		if(!check_value($this->_packageCredits)) throw new \Exception(lang('paygol_error_12'));
		
		$packages = $this->getPackageList();
		if(!is_array($packages)) {
			$packages = array();
		}
		
		$packageExists = $this->getPackageInformation();
		if(is_array($packageExists)) throw new \Exception(lang('paygol_error_13'));
		
		$packages[$this->_packageId] = array(
			'title' => $this->_packageTitle,
			'cost' => $this->_packageCost,
			'credits' => $this->_packageCredits
		);
		
		$save = $this->_updatePackagesFile($packages);
		if(!$save) throw new \Exception(lang('paygol_error_14'));
	}
	
	public function deletePackage() {
		if(!check_value($this->_packageId)) throw new \Exception(lang('paygol_error_15'));
		
		$packageExists = $this->getPackageInformation();		
		if(!is_array($packageExists)) throw new \Exception(lang('paygol_error_16'));
		
		$packages = $this->getPackageList();
		unset($packages[$this->_packageId]);
		
		$save = $this->_updatePackagesFile($packages);
		if(!$save) throw new \Exception(lang('paygol_error_14'));
	}
	
	public function getPackageInformation() {
		if(!check_value($this->_packageId)) return;
		$packages = $this->getPackageList();
		if(!is_array($packages)) return;
		if(!array_key_exists($this->_packageId, $packages)) return;
		return $packages[$this->_packageId];
	}
	
	public function getPackageList() {
		$packages = file_get_contents(__PATH_PAYGOL_ROOT__ . $this->_packagesFile);
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
	
	public function getServiceId() {
		return $this->_serviceId;
	}
	
	public function getSharedSecret() {
		return $this->_sharedSecret;
	}
	
	public function processPayment($data=array()) {
		
		if(!$this->_validateRequest($data)) return;
		
		$db = $this->_getAccountsDatabaseObject();
		
		$duplicateTransaction = $db->query_fetch_single("SELECT * FROM ".$this->_sqlTable." WHERE transaction_id = ?", array($data['transaction_id']));
		if(is_array($duplicateTransaction)) return;
		
		$custom = explode('-', $data['custom']);
		if(!is_array($custom)) return;
		
		$package = $custom[0];
		$userid = $custom[1];
		
		$this->setPackageId($package);
		$packageInfo = $this->getPackageInformation();
		if(!is_array($packageInfo)) return;
		$credits = $packageInfo['credits'];
		
		$Account = new \Account();
		$accountData = $Account->accountInformation($userid);
		if(!is_array($accountData)) return;
		
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
			$data['transaction_id'],
			$data['country'],
			$data['price'],
			$data['currency'],
			$data['frmprice'],
			$data['frmcurrency'],
			$credits,
			$package,
			$accountData[_CLMN_USERNM_],
			$data['method']
		);
		
		$query = "INSERT INTO ".$this->_sqlTable." (transaction_id, country, price, currency, frmprice, frmcurrency, credits, package, username, method, timestamp) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";
		
		$saveLog = $db->query($query, $insertData);
		if(!$saveLog) throw new \Exception($db->error);
	}
	
	public function checkPaygolTable() {
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
		if(!file_exists(__PATH_PAYGOL_ROOT__ . $this->_modulesPath . '/' . $module . '.php')) return;
		return true;
	}
	
	private function _checkTable() {
		$db = $this->_getAccountsDatabaseObject();
		$tableExists = $db->query_fetch_single("SELECT * FROM sysobjects WHERE xtype = 'U' AND name = ?", array($this->_sqlTable));
		if($tableExists) return;
		if(!file_exists($this->sqlFilePath.$this->_sqlFileName)) throw new \Exception(lang('paygol_error_4', true));
		$query = file_get_contents($this->sqlFilePath.$this->_sqlFileName);
		if(!check_value($query)) throw new \Exception(lang('paygol_error_4', true));
		$queryFinal = str_replace('{TABLE_NAME}', $this->_sqlTable, $query);
		if(!$queryFinal) throw new \Exception(lang('paygol_error_4', true));
		if(!$db->query($queryFinal)) throw new \Exception(lang('paygol_error_4', true));
		return true;
	}
	
	private function _updatePackagesFile($data) {
		$file = __PATH_PAYGOL_ROOT__ . $this->_packagesFile;
		$fp = fopen($file, 'w');
		if(!fwrite($fp, json_encode($data, JSON_PRETTY_PRINT))) return;
		fclose($fp);
		return true;
	}
	
	private function _getAccountsDatabaseObject() {
		return \Connection::Database('Me_MuOnline');
	}
	
	private function _validateRequest($data=array()) {
		if(!check_value($data['service_id'])) return;
		if(!check_value($data['key'])) return;
		if($data['service_id'] != $this->_serviceId) return;
		if($data['key'] != $this->_sharedSecret) return;
		return true;
	}
	
}