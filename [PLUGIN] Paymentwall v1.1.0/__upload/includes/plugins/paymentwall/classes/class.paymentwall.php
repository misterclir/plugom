<?php
/**
 * PaymentWall
 * https://webenginecms.org/
 * 
 * @version 1.1.0
 * @author Lautaro Angelico <http://lautaroangelico.com/>
 * @copyright (c) 2013-2020 Lautaro Angelico, All Rights Reserved
 * @build wfd094c4936ed5b6323fd0fcab72ef68
 */

namespace Plugin\PaymentWallGateway;

class PaymentWallGateway {
	
	private $_configXml = 'config.xml';
	private $_modulesPath = 'modules';
	private $_sqlTable = WE_PREFIX . 'WEBENGINE_PAYMENTWALL_LOGS';
	private $_sqlPath = 'sql';
	private $_sqlFileName = 'WEBENGINE_PAYMENTWALL_LOGS.txt';
	
	private $_creditConfig;
	private $_projectKey;
	private $_secretKey;
	private $_widget;
	private $_widgetWidth;
	private $_widgetHeight;
	
	private $_username;
	private $_userid;
	private $_limit = 10;
	
	private $_requiredData = array(
		'uid',
		'currency',
		'type',
		'ref',
		'sig'
	);
	
	private $_isTest = 0;
	private $_transactionType = array(
		0 => 'Regular Payment',
		1 => 'Product/Virtual Currency',
		2 => 'Refund/Chargeback',
	);
	
	// CONSTRUCTOR
	
	function __construct() {
		
		// load databases
		$this->me = \Connection::Database('Me_MuOnline');
		
		// load configs
		$this->configFilePath = __PATH_PAYMENTWALL_ROOT__.$this->_configXml;
		if(!file_exists($this->configFilePath)) throw new \Exception(lang('paymentwall_error_2'));
		$xml = simplexml_load_file($this->configFilePath);
		if(!$xml) throw new \Exception(lang('paymentwall_error_2'));
		
		// set configs
		$this->_creditConfig = (int) $xml->credit_config;
		$this->_projectKey = (string) $xml->project_key;
		$this->_secretKey = (string) $xml->secret_key;
		$this->_widget = (string) $xml->widget;
		$this->_widgetWidth = (int) $xml->widget_width;
		$this->_widgetHeight = (int) $xml->widget_height;
		
		// sql file path
		$this->sqlFilePath = __PATH_PAYMENTWALL_ROOT__.$this->_sqlPath.'/';
		
		// check tables
		$this->_checkTable();
	}
	
	// PUBLIC FUNCTIONS
	
	public function loadModule($module) {
		if(!\Validator::Alpha($module)) throw new \Exception(lang('paymentwall_error_3'));
		if(!$this->_moduleExists($module)) throw new \Exception(lang('paymentwall_error_3'));
		if(!@include_once(__PATH_PAYMENTWALL_ROOT__ . $this->_modulesPath . '/' . $module . '.php')) throw new \Exception(lang('paymentwall_error_3'));
	}
	
	public function setUserid($userid) {
		$this->_userid = $userid;
	}
	
	public function setUsername($username) {
		$this->_username = $username;
	}
	
	public function getProjectKey() {
		return $this->_projectKey;
	}
	
	public function getSecretKey() {
		return $this->_secretKey;
	}
	
	public function setLimit($limit) {
		$this->_limit = $limit;
	}
	
	public function loadWidget() {
		$Account = new \Account();
		$accountData = $Account->accountInformation($this->_userid);
		if(!is_array($accountData)) throw new \Exception(lang('paymentwall_error_6'));
		
		\Paymentwall_Config::getInstance()->set(array(
			'api_type' => \Paymentwall_Config::API_VC,
			'public_key' => $this->_projectKey,
			'private_key' => $this->_secretKey
		));

		$widget = new \Paymentwall_Widget(
			$accountData[_CLMN_USERNM_], 
			$this->_widget,
			array(),
			array(
				'email' => $accountData[_CLMN_EMAIL_], 
				'timestamp' => time(),
				'ps' => 'all'
			)
		);

		echo $widget->getHtmlCode(array('width' => $this->_widgetWidth, 'height' => $this->_widgetHeight));
	}
	
	public function processPayment($data) {
		if(!is_array($data)) return;
		if(!$this->_checkRequiredData($data)) return;
		
		if($this->_transactionReferenceExists($data['ref']) === true) {
			throw new \Exception('Duplicate transaction.');
		}
		
		$Account = new \Account();
		$accountId = $Account->retrieveUserID($data['uid']);
		$accountData = $Account->accountInformation($accountId);
		if(!is_array($accountData)) throw new \Exception(lang('paymentwall_error_6'));
		
		$creditSystem = new \CreditSystem();
		$creditSystem->setConfigId($this->_creditConfig);
		$configSettings = $creditSystem->showConfigs(true);
		switch($configSettings['config_user_col_id']) {
			case 'userid':
				$creditSystem->setIdentifier($accountId);
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
		$creditSystem->addCredits(abs($data['currency']));
		
		if(array_key_exists('is_test', $data)) {
			if($data['is_test'] == 1) {
				$this->_isTest = 1;
			}
		}
		
		$this->_deliveryConfirmation($data['ref']);
		
		// save log
		$this->_saveLog($data);
	}
	
	public function processCancelation($data) {
		if(!is_array($data)) return;
		if(!$this->_checkRequiredData($data)) return;
		
		$Account = new \Account();
		$accountId = $Account->retrieveUserID($data['uid']);
		$accountData = $Account->accountInformation($accountId);
		if(!is_array($accountData)) throw new \Exception(lang('paymentwall_error_6'));
		
		$creditSystem = new \CreditSystem();
		$creditSystem->setConfigId($this->_creditConfig);
		$configSettings = $creditSystem->showConfigs(true);
		switch($configSettings['config_user_col_id']) {
			case 'userid':
				$creditSystem->setIdentifier($accountId);
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
		$creditSystem->subtractCredits(abs($data['currency']));	
		
		// save log
		$this->_saveLog($data);
	}
	
	public function getAccountLogs() {
		if(!check_value($this->_username)) return;
		$result = $this->me->query_fetch("SELECT TOP ".$this->_limit." * FROM ".$this->_sqlTable." WHERE uid = ? ORDER BY id DESC", array($this->_username));
		if(!is_array($result)) return;
		return $result;
	}
	
	public function getLogs() {
		$result = $this->me->query_fetch("SELECT TOP ".$this->_limit." * FROM ".$this->_sqlTable." ORDER BY id DESC");
		if(!is_array($result)) return;
		return $result;
	}
	
	public function returnTransactionType($type=1) {
		if(!array_key_exists($type, $this->_transactionType)) return 'Unknown';
		return $this->_transactionType[$type];
	}
	
	// PRIVATE FUNCTIONS
	
	private function _moduleExists($module) {
		if(!check_value($module)) return;
		if(!file_exists(__PATH_PAYMENTWALL_ROOT__ . $this->_modulesPath . '/' . $module . '.php')) return;
		return true;
	}
	
	private function _checkTable() {
		$tableExists = $this->me->query_fetch_single("SELECT * FROM sysobjects WHERE xtype = 'U' AND name = ?", array($this->_sqlTable));
		if($tableExists) return true;
		if(!$this->_createTable()) throw new \Exception(lang('paymentwall_error_4', true));
	}
	
	private function _createTable() {
		if(!file_exists($this->sqlFilePath.$this->_sqlFileName)) return;
		$query = file_get_contents($this->sqlFilePath.$this->_sqlFileName);
		if(!check_value($query)) return;
		$queryFinal = str_replace('{TABLE_NAME}', $this->_sqlTable, $query);
		if(!$queryFinal) return;
		if(!$this->me->query($queryFinal)) return;
		return true;
	}
	
	private function _saveLog($data) {
		$logData = array(
			'uid' => $data['uid'],
			'currency' => $data['currency'],
			'type' => $data['type'],
			'ref' => $data['ref'],
			'sig' => $data['sig']
		);
		
		$query = "INSERT INTO ".$this->_sqlTable." (uid, currency, type, ref, sig, timestamp) VALUES (:uid, :currency, :type, :ref, :sig, CURRENT_TIMESTAMP)";
		
		$log = $this->me->query($query, $logData);
		if(!$log) return;
	}
	
	private function _checkRequiredData($data) {
		foreach($this->_requiredData as $key) {
			if(!array_key_exists($key, $data)) return;
		}
		return true;
	}
	
	private function _transactionReferenceExists($ref) {
		if(!check_value($ref)) return;
		$result = $this->me->query_fetch_single("SELECT * FROM ".$this->_sqlTable." WHERE ref = ?", array($ref));
		if(!is_array($result)) return;
		return true;
	}
	
	private function _deliveryConfirmation($ref) {
		$result = $this->me->query_fetch_single("SELECT * FROM ".$this->_sqlTable." WHERE ref = ?", array($ref));
		if(!is_array($result)) return;
		
		$Account = new \Account();
		$accountId = $Account->retrieveUserID($result['uid']);
		$accountData = $Account->accountInformation($accountId);
		if(!is_array($accountData)) return;
		if(!check_value($accountData[_CLMN_EMAIL_])) return;
		
		\Paymentwall_Config::getInstance()->set(array(
			'private_key' => $this->_secretKey
		));

		$delivery = new \Paymentwall_GenerericApiObject('delivery');

		$response = $delivery->post(array(
			'payment_id' => $result['ref'],
			'merchant_reference_id' => $result['id'],
			'type' => 'digital',
			'status' => 'delivered',
			'estimated_delivery_datetime' => date("Y/m/d H:i:s O"),
			'estimated_update_datetime' => date("Y/m/d H:i:s O"),
			'refundable' => 'no',
			'details' => 'Virtual currency delivered to the user account successfully',
			'shipping_address[email]' => $accountData[_CLMN_EMAIL_],
			'reason' => null,
			'attachments' => null,
			'is_test' => $this->_isTest,
		));
		
		if(isset($response['success'])) {
		} elseif(isset($response['error'])) {
		}
	}
	
}