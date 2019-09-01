<?php
use LSYS\PayGateway\Adapter\Wechat\Bill;
include __DIR__."/../Bootstarp.php";
include_once './WxPay.Config.php';
$config=\LSYS\PayGateway\Adapter\Wechat\PayCodeConfig::arr(\LSYS\PayGateway\Adapter\Wechat\PayWapConfig::WxPayConfigToArr());
$bill= new Bill($config);
$bill->setDate("2017-1-9")->exec();
while ($result=$bill->getResult()){
	bill_callback($bill,$result);
}