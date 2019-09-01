<?php
use LSYS\PayGateway\Adapter\Palpay\PayMgr;
include __DIR__."/../Bootstarp.php";
$_config=include_once './cfg.php';
$pay=(new PayMgr(PALPAY))->payCreate($_config);
pay_callback($pay,$pay->payCallback());