<?php
use LSYS\PayGateway\Adapter\Alipay\Bill;
include __DIR__."/../Bootstarp.php";
include_once 'alipay.config.php';
$config=\LSYS\PayGateway\Adapter\Alipay\Config::arr($alipay_config);
$bill= new Bill($config);
$bill->setTransCode(array(
	'3012',
	'5004',
	'5103',
	'6001',
));
$bill->setDate("2016-12-27")->exec();
while ($result=$bill->getResult()){
	bill_callback($bill,$result);
}