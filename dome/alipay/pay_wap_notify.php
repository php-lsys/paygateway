<?php
use LSYS\PayGateway\Adapter\Alipay\PayWapMgr;
include __DIR__."/../Bootstarp.php";
include_once 'alipay.config.php';
$pay=(new PayWapMgr(ALIPAYWAP))->payCreate($alipay_config);
pay_notify($pay,$pay->payNotify()); 
