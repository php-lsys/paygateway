<?php
use LSYS\PayGateway\Adapter\Upacp\ApplePayMgr;
include __DIR__."/../Bootstarp.php";
$config=include_once 'cfg.php';
$refund=(new ApplePayMgr(UINONPAYAPPLE))->refundCreate($config);
refund_callback($refund,$refund->refundNotify());