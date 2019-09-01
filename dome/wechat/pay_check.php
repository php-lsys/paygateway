<?php
use LSYS\PayGateway\Pay\PayRender;
use LSYS\PayGateway\Pay\QueryParam;
use LSYS\PayGateway\Pay\PayResult\SuccResult;
use LSYS\PayGateway\Adapter\Wechat\PayCodeMgr;
include __DIR__."/../Bootstarp.php";
//验证登录等...
$order=PayRender::qrcodeGetSn();
if (!$order)PayRender::qrcodeOutput(false);

/*检查数据库记录是否被支付*/
$status=false;
//未被支付,调用API 检查支付状态
if (!$status){
	include_once './WxPay.Config.php';
	$config=\LSYS\PayGateway\Adapter\Wechat\PayWapConfig::WxPayConfigToArr();
	$pay=(new PayCodeMgr(WEIXINWEB))->payCreate($config);
	$param=new QueryParam($order, null, null);
	$pstatus=$pay->query($param);
	if($pstatus instanceof SuccResult){
		$status=true;
		ob_start();
		result_callback($pay,$pstatus);//支付成功,回写数据库
		ob_end_clean();
	}
}
if($status){
	PayRender::qrcodeOutput(true);
}else{
	PayRender::qrcodeOutput(false,'发生错误');	
}