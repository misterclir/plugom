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

echo '<h2>Paygol Logs</h2>';

// load configs
$pluginConfig = file_get_contents(__PATH_PAYGOL_ROOT__.'config.json');
if(!$pluginConfig) throw new Exception('Error loading config file.');
$pluginConfig = json_decode($pluginConfig, true);
if(!is_array($pluginConfig)) throw new Exception('Error loading config file.');

// credit system
$creditSystem = new CreditSystem();
$creditSystem->setConfigId($pluginConfig['credit_config']);
$configSettings = $creditSystem->showConfigs(true);
$creditsTitle = check_value($configSettings['config_title']) ? $configSettings['config_title'] : 'credits';

$PaygolDonation = new Plugin\PaygolDonation\PaygolDonation();
$packagesList = $PaygolDonation->getPackageList();

$paygolLogs = $PaygolDonation->getLogs();

echo '<div class="row">';
	echo '<div class="col-xs-12">';
	
	if(is_array($paygolLogs)) {
		echo '<table class="table table-hover">';
			echo '<thead>';
				echo '<tr>';
					echo '<th>Transaction Id</th>';
					echo '<th>Date</th>';
					echo '<th>Country</th>';
					echo '<th>Price</th>';
					echo '<th>Currency</th>';
					echo '<th>Package Price</th>';
					echo '<th>Package Currency</th>';
					echo '<th>Credits Given</th>';
					echo '<th>Method</th>';
					echo '<th>Package</th>';
					echo '<th>Account</th>';
				echo '</tr>';
			echo '</thead>';
			echo '<tbody>';
			foreach($paygolLogs as $row) {
				echo '<tr>';
					echo '<td>'.$row['transaction_id'].'</td>';
					echo '<td>'.$row['timestamp'].'</td>';
					echo '<td>'.$row['country'].'</td>';
					echo '<td>'.number_format($row['price'], 2).'</td>';
					echo '<td>'.$row['currency'].'</td>';
					echo '<td>'.number_format($row['frmprice'], 2).'</td>';
					echo '<td>'.$row['frmcurrency'].'</td>';
					echo '<td>'.number_format($row['credits']).'</td>';
					echo '<td>'.$row['method'].'</td>';
					echo '<td>'.$row['package'].'</td>';
					echo '<td>'.$row['username'].'</td>';
				echo '</tr>';
			}
			echo '</tbody>';
		echo '</table>';
	} else {
		message('warning', 'There are no paygol donation logs.');
	}
	
	echo '</div>';
echo '</div>';