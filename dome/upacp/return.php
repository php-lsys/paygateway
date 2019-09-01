<?php
use LSYS\PayGateway\Adapter\Upacp\PayMgr;
include __DIR__."/../Bootstarp.php";
$_config=include_once './cfg.php';
$pay=(new PayMgr(UINONPAY))->payCreate($_config);
pay_callback($pay,$pay->payCallback());