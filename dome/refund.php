<?php

use LSYS\PayGateway\Utils;
use LSYS\PayGateway\Pay\RefundResult\SuccResult;
use LSYS\PayGateway\Pay\RefundResult\FailResult;

require_once  __DIR__."/Bootstarp.php";
require_once  __DIR__."/pay_public.php";
$name=@$_GET['name'];
$order_no=@$_GET['pay_sn'];
$pay_no=@$_GET['pay_no'];
$create_time=@$_GET['carete_time'];

//一般从数据库里查询出来...
$name=ALIPAYWAP;//支付类型
$order_no='PD161227181618164245';//订单号
$pay_no='2016122721001004910237058479';//支付号
$total='27.500';//支付总额
$MONEY='0.49';//退款额
$mgr=LSYS\PayGateway\DI::get()->paygatewayPaymgr();
$refund=$mgr->findRefund($name);
if (!$refund){
    die('不支持退款');
}
$pay_utils=$refund->refundCreate($config[$name]['refund_config']);

//支付回调出错情况下手动同步订单状态....
//不要以次条件为是否扣款依据.只记录状态,失败最好人工介入
$param= new \LSYS\PayGateway\Pay\RefundParam($order_no, $pay_no, $total,$MONEY);
$param->setReturnNo(Utils::snnoCreate('REFUND'));//设置退款号,不设置自动生成
$result=$pay_utils->refund($param);
if ($result instanceof SuccResult){
	print_r($result);//完成退款
}else if ($result instanceof FailResult){
	print_r($result);//退款申请中,可能退款还没完成..
}else{
	echo "退款失败:";
	print_r($result->getMsg());//完成退款
}


