<?php
use LSYS\PayGateway\Adapter\JD\PayWebMgr;
include __DIR__."/../Bootstarp.php";
$config=include_once 'cfg.php';
$pay=(new PayWebMgr(JDWEB))->payCreate($config);
pay_notify($pay,$pay->payNotify()); 