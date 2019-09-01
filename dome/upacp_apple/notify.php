<?php
use LSYS\PayGateway\Adapter\Upacp\ApplePayMgr;
include __DIR__."/../Bootstarp.php";
$_config=include_once './cfg.php';
$pay=(new ApplePayMgr(UINONPAYAPPLE))->payCreate($_config);
pay_notify($pay,$pay->payNotify()); 