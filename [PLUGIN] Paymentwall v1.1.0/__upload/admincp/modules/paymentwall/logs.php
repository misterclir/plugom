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

echo '<h1 class="page-header">PaymentWall Logs</h1>';

$PaymentWallGateway = new \Plugin\PaymentWallGateway\PaymentWallGateway();
$PaymentWallGateway->setLimit(1000);
$logs = $PaymentWallGateway->getLogs();

echo '<div class="row">';

	echo '<div class="col-md-12">';
		
		echo '<div class="panel panel-default">';
		echo '<div class="panel-heading">Logs</div>';
		echo '<div class="panel-body">';
			if(is_array($logs)) {
				echo '<table class="table table-hover">';
				echo '<thead>';
					echo '<tr>';
						echo '<th>Order Id</th>';
						echo '<th>Account</th>';
						echo '<th>Currency</th>';
						echo '<th>Type</th>';
						echo '<th>Reference</th>';
						echo '<th>Signature</th>';
						echo '<th>Date</th>';
					echo '</tr>';
				echo '</thead>';
				echo '<tbody>';
				foreach($logs as $row) {
					echo '<tr>';
						echo '<td>'.$row['id'].'</td>';
						echo '<td>'.$row['uid'].'</td>';
						echo '<td>'.$row['currency'].'</td>';
						echo '<td>'.$PaymentWallGateway->returnTransactionType($row['type']).'</td>';
						echo '<td>'.$row['ref'].'</td>';
						echo '<td>'.$row['sig'].'</td>';
						echo '<td>'.$row['timestamp'].'</td>';
					echo '</tr>';
				}
				echo '
				</tbody>
				</table>';
			} else {
				message('warning', 'There are no logs to display.');
			}
		echo '</div>';
		echo '</div>';
		
	echo '</div>';
echo '</div>';