<?php
require_once  __DIR__."/Bootstarp.php";

include_once 'wechat/WxPay.Config.php';

$mgr=LSYS\PayGateway\DI::get()->paygatewayTransfersmgr();


include_once 'alipay/alipay.config.php';

$config=array(
    "alipay"=>array(
        'adapter'=>\LSYS\PayGateway\Adapter\Alipay\TransfersMgr::class,
        'config'=>$alipay_config,
    ),
    "weixin"=>array(
        'adapter'=>\LSYS\PayGateway\Adapter\Wechat\TransfersMgr::class,
        'config'=>\LSYS\PayGateway\Adapter\Wechat\PayWapConfig::WxPayConfigToArr(),
    ),
);
foreach ($config as $k=>$v){
    $mgr->add((new ReflectionClass($v['adapter']))->newInstance($k));
}
return $mgr;