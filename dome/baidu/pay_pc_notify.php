<?php
use LSYS\PayGateway\Adapter\Baidu\PayWebMgr;
include __DIR__."/../Bootstarp.php";
$config=include_once 'cfg.php';
$pay=(new PayWebMgr(BAIDUWEB))->payCreate($config);
pay_notify($pay,$pay->payNotify()); 