<?php
use LSYS\PayGateway\Adapter\Qpay\PayAppMgr;
include __DIR__."/../Bootstarp.php";
include_once './qqPay.Config.php';
$config=(new \ReflectionClass('\QQPayConfig'))->getConstants();
$pay=(new PayAppMgr(QPAYAPP))->payCreate($config);
pay_notify($pay,$pay->payNotify()); ;