<?php
use LSYS\PayGateway\Adapter\Baidu\PayBankMgr;
include __DIR__."/../Bootstarp.php";
$config=include_once 'cfg.php';
$pay=(new PayBankMgr(BAIDUBANK))->payCreate($config);
pay_notify($pay,$pay->payNotify()); 