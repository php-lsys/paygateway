<?php
use LSYS\PayGateway\Adapter\Alipay\TransfersMgr;
include __DIR__."/../Bootstarp.php";
include_once 'alipay.config.php';
$transfers=(new TransfersMgr("alipay"))->transfersCreate($alipay_config);
transfers_callback($transfers,$transfers->transfersNotify());