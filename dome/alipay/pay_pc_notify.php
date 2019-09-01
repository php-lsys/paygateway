<?php
use LSYS\PayGateway\Adapter\Alipay\PayWebMgr;
include __DIR__."/../Bootstarp.php";
include_once 'alipay.config.php';
$pay=(new PayWebMgr(ALIPAYPC))->payCreate($alipay_config);
pay_notify($pay,$pay->payNotify());
$pay->payNotifyOutput(true);