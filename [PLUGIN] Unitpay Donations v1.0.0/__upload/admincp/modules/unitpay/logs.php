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

echo '<h2>Unitpay Logs</h2>';

// load configs
$pluginConfig = file_get_contents(__PATH_UNITPAY_ROOT__.'config.json');
if(!$pluginConfig) throw new Exception('Error loading config file.');
$pluginConfig = json_decode($pluginConfig, true);
if(!is_array($pluginConfig)) throw new Exception('Error loading config file.');

// credit system
$creditSystem = new CreditSystem();
$creditSystem->setConfigId($pluginConfig['credit_config']);
$configSettings = $creditSystem->showConfigs(true);
$creditsTitle = check_value($configSettings['config_title']) ? $configSettings['config_title'] : 'credits';

$UnitpayDonation = new Plugin\UnitpayDonation\UnitpayDonation();
$packagesList = $UnitpayDonation->getPackageList();

$unitpayLogs = $UnitpayDonation->getLogs();

echo '<div class="row">';
	echo '<div class="col-xs-12">';
	
	if(is_array($unitpayLogs)) {
		echo '<table class="table table-hover">';
			echo '<thead>';
				echo '<tr>';
					echo '<th>Unitpay Id</th>';
					echo '<th>Date</th>';
					echo '<th>Operator</th>';
					echo '<th>Order Sum</th>';
					echo '<th>Currency</th>';
					echo '<th>Payment Type</th>';
					echo '<th>Signature</th>';
					echo '<th>Credits</th>';
					echo '<th>Package</th>';
					echo '<th>Account</th>';
				echo '</tr>';
			echo '</thead>';
			echo '<tbody>';
			foreach($unitpayLogs as $row) {
				echo '<tr>';
					echo '<td>'.$row['transaction_id'].'</td>';
					echo '<td>'.$row['timestamp'].'</td>';
					echo '<td>'.$row['operator'].'</td>';
					echo '<td>'.number_format($row['order_sum'], 2).'</td>';
					echo '<td>'.$row['order_currency'].'</td>';
					echo '<td>'.$row['payment_type'].'</td>';
					echo '<td>'.$row['signature'].'</td>';
					echo '<td>'.number_format($row['credits']).'</td>';
					echo '<td>'.$row['package'].'</td>';
					echo '<td>'.$row['username'].'</td>';
				echo '</tr>';
			}
			echo '</tbody>';
		echo '</table>';
	} else {
		message('warning', 'There are no unitpay donation logs.');
	}
	
	echo '</div>';
echo '</div>';