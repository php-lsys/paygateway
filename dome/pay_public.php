<?php
require_once  __DIR__."/Bootstarp.php";

$mgr=LSYS\PayGateway\DI::get()->paygatewayPaymgr();


include_once 'wechat/WxPay.Config.php';
include_once 'qpay/qqPay.Config.php';


$_upacp_apple_config=include_once './upacp_apple/cfg.php';
$_upacp_config=include_once './upacp/cfg.php';
$_jdpay_config=include_once './jdpay/cfg.php';
$_baidu_config=include_once './baidu/cfg.php';
$_palpay_config=include_once './palpay/cfg.php';
include_once 'alipay/alipay.config.php';

$config=array(
    ALIPAYWAP=>array(
        'adapter'=>\LSYS\PayGateway\Adapter\Alipay\PayWapMgr::class,
        'pay_config'=>$alipay_config,
        'refund_config'=>$alipay_config,
    ),
    ALIPAYPC=>array(
        'adapter'=>\LSYS\PayGateway\Adapter\Alipay\PayWebMgr::class,
        'pay_config'=>$alipay_config,
        'refund_config'=>$alipay_config,
    ),
    ALIPAYAPP=>array(
        'adapter'=>\LSYS\PayGateway\Adapter\Alipay\PayAppMgr::class,
        'pay_config'=>$alipay_config,
        'refund_config'=>$alipay_config,
    ),
    BAIDUBANK=>array(
        'adapter'=>\LSYS\PayGateway\Adapter\Baidu\PayBankMgr::class,
        'pay_config'=>$_baidu_config,
        'refund_config'=>$_baidu_config,
    ),
    BAIDUWAP=>array(
        'adapter'=>\LSYS\PayGateway\Adapter\Baidu\PayWapMgr::class,
        'pay_config'=>$_baidu_config,
        'refund_config'=>$_baidu_config,
    ),
    BAIDUWEB=>array(
        'adapter'=>\LSYS\PayGateway\Adapter\Baidu\PayWebMgr::class,
        'pay_config'=>$_baidu_config,
        'refund_config'=>$_baidu_config,
    ),
    JDWAP=>array(
        'adapter'=>\LSYS\PayGateway\Adapter\JD\PayWapMgr::class,
        'pay_config'=>$_jdpay_config,
        'refund_config'=>$_jdpay_config,
    ),
    JDWEB=>array(
        'adapter'=>\LSYS\PayGateway\Adapter\JD\PayWebMgr::class,
        'pay_config'=>$_jdpay_config,
        'refund_config'=>$_jdpay_config,
    ),
    PALPAYDIRECT=>array(
        'adapter'=>\LSYS\PayGateway\Adapter\Palpay\DirectPayMgr::class,
        'pay_config'=>$_palpay_config,
        'refund_config'=>$_palpay_config,
    ),
    PALPAY=>array(
        'adapter'=>\LSYS\PayGateway\Adapter\Palpay\PayMgr::class,
        'pay_config'=>$_palpay_config,
        'refund_config'=>$_palpay_config,
    ),
    QPAYWAP=>array(
        'adapter'=>\LSYS\PayGateway\Adapter\Qpay\PayWapMgr::class,
        'pay_config'=>(new \ReflectionClass('\QQPayConfig'))->getConstants(),
        'refund_config'=>(new \ReflectionClass('\QQPayConfig'))->getConstants(),
    ),
    UINONPAY=>array(
        'adapter'=>\LSYS\PayGateway\Adapter\Upacp\PayMgr::class,
        'pay_config'=>$_upacp_config,
        'refund_config'=>$_upacp_config,
    ),
    UINONPAYAPPLE=>array(
        'adapter'=>\LSYS\PayGateway\Adapter\Upacp\ApplePayMgr::class,
        'pay_config'=>$_upacp_apple_config,
        'refund_config'=>$_upacp_apple_config,
    ),
    WEIXINAPP=>array(
        'adapter'=>\LSYS\PayGateway\Adapter\Wechat\PayAppMgr::class,
        'pay_config'=>\LSYS\PayGateway\Adapter\Wechat\PayWapConfig::WxPayConfigToArr(),
        'refund_config'=>\LSYS\PayGateway\Adapter\Wechat\PayWapConfig::WxPayConfigToArr(),
    ),
    WEIXINWEB=>array(
        'adapter'=>\LSYS\PayGateway\Adapter\Wechat\PayCodeMgr::class,
        'pay_config'=>\LSYS\PayGateway\Adapter\Wechat\PayWapConfig::WxPayConfigToArr(),
        'refund_config'=>\LSYS\PayGateway\Adapter\Wechat\PayWapConfig::WxPayConfigToArr(),
    ),
    WEIXINWAP=>array(
        'adapter'=>\LSYS\PayGateway\Adapter\Wechat\PayWapMgr::class,
        'pay_config'=>\LSYS\PayGateway\Adapter\Wechat\PayWapConfig::WxPayConfigToArr(),
        'refund_config'=>\LSYS\PayGateway\Adapter\Wechat\PayWapConfig::WxPayConfigToArr(),
    ),
);

foreach ($config as $k=>$v){
    $mgr->add((new ReflectionClass($v['adapter']))->newInstance($k));
}

return $mgr;