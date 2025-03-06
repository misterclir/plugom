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

if(!isLoggedIn()) redirect(1,'login');

echo '<div class="page-title"><span>'.lang('paygol_title',true).'</span></div>';
echo '<div class="text-center" style="margin-bottom:20px;"><img src="'.__PATH_TEMPLATE_IMG__.'paygol_logo.png" width="60%" height="auto" /></div>';

if(check_value($_GET['success'])) {
	message('success', lang('paygol_success_1'));
}

try {
	
	$packagesList = $PaygolDonation->getPackageList();
	if(!is_array($packagesList)) throw new Exception(lang('paygol_error_17'));
	
	$creditsTitle = $PaygolDonation->getCreditsTitle();
	if(!check_value($creditsTitle)) throw new Exception(lang('paygol_error_19'));
	
	echo '<table class="table">';
		echo '<thead>';
			echo '<tr>';
				echo '<th class="text-center">'.lang('paygol_txt_1').'</th>';
				echo '<th class="text-center">'.lang('paygol_txt_2').'</th>';
				echo '<th class="text-center">'.lang('paygol_txt_3').'</th>';
				echo '<th class="text-center"></th>';
			echo '</tr>';
		echo '</thead>';
		echo '<thead>';
		foreach($packagesList as $packageId => $packageInfo) {
			echo '<tr>';
				echo '<td class="text-center">'.utf8_decode($packageInfo['title']).'</td>';
				echo '<td class="text-center">'.number_format($packageInfo['credits']).' '.$creditsTitle.'</td>';
				echo '<td class="text-center">'.$PaygolDonation->getCurrencySymbol().$packageInfo['cost'].' '.$PaygolDonation->getCurrency().'</td>';
				echo '<td class="text-center"><a href="'.__PAYGOL_HOME__.'order/id/'.$packageId.'" class="btn btn-sm btn-primary"/>'.lang('paygol_txt_4').'</a></td>';
			echo '</tr>';
		}
		echo '</thead>';
	echo '</table>';
	
} catch(Exception $ex) {
	message('error', $ex->getMessage());
}