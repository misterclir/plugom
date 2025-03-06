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

try {
	if(!class_exists('Plugin\PaymentWallGateway\PaymentWallGateway')) throw new Exception('Plugin disabled.');
	$PaymentWallGateway = new Plugin\PaymentWallGateway\PaymentWallGateway();
	$PaymentWallGateway->loadModule('paymentwall');
} catch(Exception $ex) {
	message('error', $ex->getMessage());
}