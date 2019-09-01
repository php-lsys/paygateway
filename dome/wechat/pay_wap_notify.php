<?php
use LSYS\PayGateway\Adapter\Wechat\PayWapMgr;
include __DIR__."/../Bootstarp.php";
include_once './WxPay.Config.php';
$config=\LSYS\PayGateway\Adapter\Wechat\PayWapConfig::WxPayConfigToArr();
$pay=(new PayWapMgr(WEIXINWAP))->payCreate($config);

pay_notify($pay,$pay->payNotify()); 