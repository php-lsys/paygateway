<?php
use LSYS\PayGateway\Pay\PayResult\SuccResult;
use LSYS\PayGateway\Pay\Query;
use LSYS\PayGateway\Pay\QueryParam;
require_once  __DIR__."/Bootstarp.php";
require_once  __DIR__."/pay_public.php";
$name=@$_GET['name'];
$order_no=@$_GET['pay_sn'];
$pay_no=@$_GET['pay_no'];
$create_time=@$_GET['carete_time'];

//一般从数据库里查询出来...
// $name='lpay_upacp';//支付类型
// $order_no='MY161209140438593';//订单号
// $pay_no='201612091404380817618';//支付号
// $create_time=time();//订单创建时间

$name=ALIPAYWAP;//支付类型
$order_no='MY161227155854705';//订单号
$pay_no='2016122721001004910236848403';//支付号
$create_time=time();//订单创建时间
$mgr=LSYS\PayGateway\DI::get()->paygatewayPaymgr();
$pay=$mgr->find($name);
if (!$pay){
    die('支付方式错误');
}

$pay=$pay->payCreate($config[$name]['pay_config']);
if(!$pay instanceof Query){
    die('不支持查询');
}
$param= new QueryParam($order_no, $pay_no, $create_time);
$result = $pay->query($param);
if ($result instanceof SuccResult){
    result_callback($pay,$result);//完成交易...
}else{
    print_r($result->getRaw());
}


