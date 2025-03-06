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

function saveChanges() {
    global $_POST;
	
    $xmlPath = __PATH_PAYMENTWALL_ROOT__.'config.xml';
    $xml = simplexml_load_file($xmlPath);
	
	if(!is_writable($xmlPath)) throw new Exception('The configuration file is not writable.');

	if(!Validator::UnsignedNumber($_POST['setting_1'])) throw new Exception('Submitted setting is not valid (credit_config)');
	$xml->credit_config = $_POST['setting_1'];
	
	$xml->project_key = $_POST['setting_2'];
	$xml->secret_key = $_POST['setting_3'];
	$xml->widget = $_POST['setting_4'];
	
	if(!Validator::UnsignedNumber($_POST['setting_5'])) throw new Exception('Submitted setting is not valid (widget_width)');
	$xml->widget_width = $_POST['setting_5'];
	
	if(!Validator::UnsignedNumber($_POST['setting_6'])) throw new Exception('Submitted setting is not valid (widget_height)');
	$xml->widget_height = $_POST['setting_6'];
	
    $save = @$xml->asXML($xmlPath);
	if(!$save) throw new Exception('There has been an error while saving changes.');
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
$pluginConfig = simplexml_load_file(__PATH_PAYMENTWALL_ROOT__.'config.xml');
if(!$pluginConfig) throw new Exception('Error loading config file.');

// credit system
$creditSystem = new CreditSystem();

?>
<h2>PaymentWall Settings</h2>

<div class="alert alert-info" role="alert">
	<strong>Make sure your Paymentwall project settings match the following:</strong><br />
	<ul>
		<li>Project URL: <?php echo __BASE_URL__; ?></li>
		<li>Your API: Widget API - Virtual Currency</li>
		<li>Pingback type: URL</li>
		<li>Pingback URL: <?php echo __BASE_URL__; ?>api/paymentwall.php</li>
		<li>Pingback signature version: 1</li>
	</ul>
</div>

<form action="" method="post">

	<table class="table table-striped table-bordered table-hover module_config_tables">
		<tr>
			<th>Credit Configuration<br/><span>Type of credits the player will receive when donating.</span></th>
			<td>
				<?php echo $creditSystem->buildSelectInput("setting_1", $pluginConfig->credit_config, "form-control"); ?>
			</td>
		</tr>
        <tr>
            <th>Project Key<br/><span></span></th>
            <td>
				<input class="form-control" type="text" name="setting_2" value="<?php echo $pluginConfig->project_key; ?>"/>
            </td>
        </tr>
        <tr>
            <th>Secret Key<br/><span></span></th>
            <td>
				<input class="form-control" type="text" name="setting_3" value="<?php echo $pluginConfig->secret_key; ?>"/>
            </td>
        </tr>
        <tr>
            <th>Widget<br/><span>Paymentwall's widget type</span></th>
            <td>
				<input class="form-control" type="text" name="setting_4" value="<?php echo $pluginConfig->widget; ?>"/>
            </td>
        </tr>
        <tr>
            <th>Widget Width<br/><span>Widget width in pixels</span></th>
            <td>
				<input class="form-control" type="text" name="setting_5" value="<?php echo $pluginConfig->widget_width; ?>"/>
            </td>
        </tr>
        <tr>
            <th>Widget Height<br/><span>Widget height in pixels</span></th>
            <td>
				<input class="form-control" type="text" name="setting_6" value="<?php echo $pluginConfig->widget_height; ?>"/>
            </td>
        </tr>
		<tr>
            <td colspan="2"><input type="submit" name="submit_changes" value="Save Changes" class="btn btn-success"/></td>
        </tr>
    </table>
</form>