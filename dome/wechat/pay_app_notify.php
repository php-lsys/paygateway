<?php
use LSYS\PayGateway\Adapter\Wechat\PayAppMgr;
include __DIR__."/../Bootstarp.php";
include_once './WxPay.Config.php';
$config=\LSYS\PayGateway\Adapter\Wechat\PayWapConfig::WxPayConfigToArr();
$pay=(new PayAppMgr(WEIXINAPP))->payCreate($config);
pay_notify($pay,$pay->payNotify()); ;