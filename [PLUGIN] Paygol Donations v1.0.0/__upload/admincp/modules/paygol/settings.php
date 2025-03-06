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

function saveChanges() {
    global $_POST;
	
    $cfgFile = __PATH_PAYGOL_ROOT__.'config.json';
	if(!is_writable($cfgFile)) throw new Exception('The configuration file is not writable.');
	$pluginConfig = file_get_contents($cfgFile);
	if(!$pluginConfig) throw new Exception('Error loading config file.');
	$pluginConfig = json_decode($pluginConfig, true);
	if(!is_array($pluginConfig)) throw new Exception('Error loading config file.');
	
	if(!Validator::UnsignedNumber($_POST['setting_1'])) throw new Exception('Submitted setting is not valid (active)');
	if(!in_array($_POST['setting_1'], array(1, 0))) throw new Exception('Submitted setting is not valid (active)');
	$pluginConfig['active'] = $_POST['setting_1'];
	
	if(!Validator::UnsignedNumber($_POST['setting_2'])) throw new Exception('Submitted setting is not valid (credit_config)');
	$pluginConfig['credit_config'] = $_POST['setting_2'];
	
	if(!check_value($_POST['setting_3'])) throw new Exception('Submitted setting is not valid (currency)');
	$pluginConfig['currency'] = strtoupper($_POST['setting_3']);
	
	$pluginConfig['currency_symbol'] = utf8_encode($_POST['setting_6']);
	
	if(check_value($_POST['setting_4'])) if(!Validator::UnsignedNumber($_POST['setting_4'])) throw new Exception('Submitted setting is not valid (service_id)');
	$pluginConfig['service_id'] = $_POST['setting_4'];
	
	if(check_value($_POST['setting_5'])) if(!Validator::Chars($_POST['setting_5'], array('a-z', 'A-Z', '0-9', '-'))) throw new Exception('Submitted setting is not valid (shared_secret)');
	$pluginConfig['shared_secret'] = $_POST['setting_5'];
	
	$fp = fopen($cfgFile, 'w');
	if(!fwrite($fp, json_encode($pluginConfig, JSON_PRETTY_PRINT))) throw new Exception('There has been an error while saving changes.');
	fclose($fp);
}

if(check_value($_POST['submit_changes'])) {
	try {
		saveChanges();
		message('success', 'Settings successfully saved.');
	} catch (Exception $ex) {
		message('error', $ex->getMessage());
	}
}

// load configs
$pluginConfig = file_get_contents(__PATH_PAYGOL_ROOT__.'config.json');
if(!$pluginConfig) throw new Exception('Error loading config file.');
$pluginConfig = json_decode($pluginConfig, true);
if(!is_array($pluginConfig)) throw new Exception('Error loading config file.');

// credit system
$creditSystem = new CreditSystem();

// Paygol
$PaygolDonation = new \Plugin\PaygolDonation\PaygolDonation();
$PaygolDonation->checkPaygolTable();
?>
<h2>Paygol Settings</h2>
<form action="" method="post">
	<table class="table table-striped table-bordered table-hover module_config_tables">
		<tr>
            <th>Status<br/><span>You may enable or disable the Paygol donation system with this setting.</span></th>
            <td>
                <?php enabledisableCheckboxes('setting_1', $pluginConfig['active'], 'Enabled', 'Disabled'); ?>
            </td>
        </tr>
		<tr>
			<th>Credit Configuration<br/><span>Select the type of credits the player will receive when donating with Paygol.</span></th>
			<td>
				<?php echo $creditSystem->buildSelectInput("setting_2", $pluginConfig['credit_config'], "form-control"); ?>
			</td>
		</tr>
		<tr>
            <th>Currency<br/><span>Type the desired currency to use for donations.<br /><br />Currency List:<br /><a href="https://www.xe.com/currency/" target="_blank">https://www.xe.com/currency/</a></span></th>
            <td>
                <input class="form-control" type="text" name="setting_3" value="<?php echo $pluginConfig['currency']; ?>"/>
            </td>
        </tr>
		<tr>
            <th>Currency Symbol<br/><span>The symbol of your currency.</span></th>
            <td>
                <input class="form-control" type="text" name="setting_6" value="<?php echo utf8_decode($pluginConfig['currency_symbol']); ?>"/>
            </td>
        </tr>
		<tr>
            <th>Paygol Service ID<br/><span>Your Paygol service id.</span></th>
            <td>
                <input class="form-control" type="text" name="setting_4" value="<?php echo $pluginConfig['service_id']; ?>"/>
            </td>
        </tr>
		<tr>
            <th>Paygol Shared Secret<br/><span>Your Paygol shared secret.</span></th>
            <td>
                <input class="form-control" type="text" name="setting_5" value="<?php echo $pluginConfig['shared_secret']; ?>"/>
            </td>
        </tr>
		<tr>
            <td colspan="2"><input type="submit" name="submit_changes" value="Save Changes" class="btn btn-success"/></td>
        </tr>
    </table>
</form>