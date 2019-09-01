<?php
use LSYS\PayGateway\Adapter\Baidu\PayWapMgr;
include __DIR__."/../Bootstarp.php";
$config=include_once 'cfg.php';
$pay=(new PayWapMgr(BAIDUWAP))->payCreate($config);
pay_callback($pay,$pay->payCallback());