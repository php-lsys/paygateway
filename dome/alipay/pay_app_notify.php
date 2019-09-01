<?php
use LSYS\PayGateway\Adapter\Alipay\PayAppMgr;
include __DIR__."/../Bootstarp.php";
include_once 'alipay.config.php';
$pay=(new PayAppMgr(ALIPAYWAP))->payCreate($alipay_config);
pay_notify($pay,$pay->payNotify());