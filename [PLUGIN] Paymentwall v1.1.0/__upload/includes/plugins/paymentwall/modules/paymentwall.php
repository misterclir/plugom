<?php
/**
 * PaymentWall
 * https://webenginecms.org/
 * 
 * @version 1.0.0
 * @author Lautaro Angelico <http://lautaroangelico.com/>
 * @copyright (c) 2013-2019 Lautaro Angelico, All Rights Reserved
 * @build wfd094c4936ed5b6323fd0fcab72ef68
 */

if(!isLoggedIn()) redirect(1,'login');

echo '<div class="page-title"><span>'.lang('module_titles_txt_11',true).' &rarr; '.lang('paymentwall_title',true).'</span></div>';

try {
	
	$PaymentWallGateway = new \Plugin\PaymentWallGateway\PaymentWallGateway();
	$PaymentWallGateway->setUserid($_SESSION['userid']);
	$PaymentWallGateway->loadWidget();
	
	echo '<h2>'.lang('paymentwall_txt_1').'</h2>';
	
	$PaymentWallGateway->setUsername($_SESSION['username']);
	$PaymentWallGateway->setLimit(10);
	$logs = $PaymentWallGateway->getAccountLogs();
	if(is_array($logs)) {
		
		echo '<table class="table">';
			echo '<thead>';
				echo '<tr>';
					echo '<th>Account</th>';
					echo '<th>Currency</th>';
					echo '<th>Ref</th>';
					echo '<th>Date</th>';
				echo '</tr>';
			echo '</thead>';
			echo '</tbody>';
			foreach($logs as $row) {
				echo '<tr>';
					echo '<td>'.$row['uid'].'</td>';
					echo '<td>'.number_format($row['currency']).'</td>';
					echo '<td>'.$row['ref'].'</td>';
					echo '<td>'.$row['timestamp'].'</td>';
				echo '</tr>';
			}
			echo '</tbody>';
		echo '</table>';
		
	} else {
		message('error', lang('paymentwall_error_5'));
	}
	
} catch(Exception $ex) {
	message('error', $ex->getMessage());
}