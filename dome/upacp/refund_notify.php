<?php
use LSYS\PayGateway\Adapter\Upacp\PayMgr;

include __DIR__."/../Bootstarp.php";
$config=include_once 'cfg.php';


$refund=(new PayMgr(UINONPAY))->refundCreate($config);
refund_callback($refund,$refund->refundNotify());

