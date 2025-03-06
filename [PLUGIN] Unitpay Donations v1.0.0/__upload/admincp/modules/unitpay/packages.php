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

echo '<h2>Unitpay Packages</h2>';

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

// remove package
if(check_value($_GET['delete'])) {
	try {
		$UnitpayDonation->setPackageId($_GET['delete']);
		$UnitpayDonation->deletePackage();
		header('Location: ' . admincp_base('unitpay&page=packages'));
	} catch(Exception $ex) {
		message('error', $ex->getMessage());
	}
}

// add package
if(check_value($_POST['package_submit'])) {
	try {
		$UnitpayDonation->setPackageId($_POST['package_id']);
		$UnitpayDonation->setPackageTitle($_POST['package_title']);
		$UnitpayDonation->setPackageCost($_POST['package_cost']);
		$UnitpayDonation->setPackageCredits($_POST['package_credits']);
		$UnitpayDonation->addNewPackage();
		header('Location: ' . admincp_base('unitpay&page=packages'));
	} catch(Exception $ex) {
		message('error', $ex->getMessage());
	}
}

echo '<div class="row">';
	
	// new package
	echo '<div class="col-xs-12 col-md-4">';
		echo '<div class="panel panel-primary">';
		echo '<div class="panel-heading">New Unitpay Package</div>';
		echo '<div class="panel-body">';

			echo '<form role="form" action="'.admincp_base('unitpay&page=packages').'" method="post">';
				echo '<div class="form-group">';
					echo '<label for="input_1">Package Id:</label>';
					echo '<input type="text" class="form-control" id="input_1" name="package_id" placeholder="Example: mypackage1"/>';
				echo '</div>';
				echo '<div class="form-group">';
					echo '<label for="input_2">Package Title:</label>';
					echo '<input type="text" class="form-control" id="input_2" name="package_title" placeholder="Example: My Package 1"/>';
				echo '</div>';
				echo '<div class="form-group">';
					echo '<label for="input_3">Package Cost:</label>';
					echo '<input type="text" class="form-control" id="input_3" name="package_cost" placeholder="100.00"/>';
				echo '</div>';
				echo '<div class="form-group">';
					echo '<label for="input_4">Package Credits:</label>';
					echo '<input type="text" class="form-control" id="input_4" name="package_credits" placeholder="1000"/>';
				echo '</div>';

				echo '<button type="submit" name="package_submit" value="1" class="btn btn-primary">Create Package</button>';
			echo '</form>';

		echo '</div>';
		echo '</div>';
	echo '</div>';
	
	// package list
	echo '<div class="col-xs-12 col-md-8">';
		echo '<div class="panel panel-default">';
		echo '<div class="panel-heading">Unitpay Packages</div>';
		echo '<div class="panel-body">';
			if(is_array($packagesList)) {
				echo '<table class="table table-hover table-striped">';
					echo '<thead>';
						echo '<tr>';
							echo '<th>Package Id</th>';
							echo '<th>Package Title</th>';
							echo '<th>Package Cost</th>';
							echo '<th>Package Credits</th>';
							echo '<th></th>';
						echo '</tr>';
					echo '</thead>';
					echo '<tbody>';
					foreach($packagesList as $packageId => $packageInfo) {
						echo '<tr>';
							echo '<td>'.$packageId.'</td>';
							echo '<td>'.utf8_decode($packageInfo['title']).'</td>';
							echo '<td>'.utf8_decode($pluginConfig['currency_symbol']).''.number_format($packageInfo['cost'], 2).' '.$pluginConfig['currency'].'</td>';
							echo '<td>'.number_format($packageInfo['credits']).' '.$creditsTitle.'</td>';
							echo '<td><a href="'.admincp_base('unitpay&page=packages&delete='.$packageId).'" class="btn btn-xs btn-danger">Remove</a></td>';
						echo '</tr>';
					}
					echo '</tbody>';
				echo '</table>';
			} else {
				message('warning', 'There are no donation packages.');
			}
		echo '</div>';
		echo '</div>';
	echo '</div>';
echo '</div>';