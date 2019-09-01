<?php
use LSYS\PayGateway\Adapter\JD\PayWebMgr;
use LSYS\PayGateway\Adapter\JD\PayWapMgr;
include __DIR__."/../Bootstarp.php";
$config=include_once 'cfg.php';
$type=JDWEB;
switch ($type){
    case JDWEB:
        $refund=(new PayWebMgr($type))->refundCreate($config);
        break;
    case JDWAP:
        $refund=(new PayWapMgr($type))->refundCreate($config);
        break;
    default:die('bad');
}
refund_callback($refund,$refund->refundNotify());