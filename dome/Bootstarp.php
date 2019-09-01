<?php
use LSYS\PayGateway\Pay\PayResult;

use LSYS\PayGateway\Pay\Money;
use LSYS\PayGateway\Pay\RefundResult;
use LSYS\PayGateway\Transfers\TransfersResult;
use LSYS\PayGateway\Pay\PayAdapterNotify;
use LSYS\PayGateway\Bill\Result;
use LSYS\PayGateway\Pay\RefundResult\SuccResult;
use LSYS\PayGateway\Pay\RefundResult\FailResult;
use LSYS\PayGateway\Loger;
use LSYS\PayGateway\Loger\Stroage\File;
include_once __DIR__."/../vendor/autoload.php";


//这些不一定要定义常量,如果存支付类型存数据库,可用数据库记录值代替,支付类型存文件,可自定义你的常量代替下面常量值
define("ALIPAYWAP", "alipaywap");
define("ALIPAYPC", "alipaypc");
define("ALIPAYAPP", "alipayapp");
define("BAIDUWAP", "baiduwap");
define("BAIDUWEB", "baiduweb");
define("BAIDUBANK", "baidubank");
define("JDWAP", "jdwap");
define("JDWEB", "jdweb");
define("PALPAYDIRECT", "palpaydirect");
define("PALPAY", "palpay");
define("QPAYAPP", "qpayapp");
define("QPAYWAP", "qpaywap");
define("UINONPAY", "uinonpay");
define("UINONPAYAPPLE", "uinonpayapple");
define("WEIXINAPP", "weixinapp");
define("WEIXINWEB", "weixinweb");
define("WEIXINWAP", "weixinwap");

$paylog=new Loger(new File(__DIR__."/log",FALSE));
//回调基本路径
define("CALLBACK_PATH", "http://localhost/lpaygateway-dome/dome/");
//支付统一处理.....
function payCallback($pay,PayResult $pay_result){
	//支付完成回调
    global $paylog;
    $paylog->add(get_class($pay), $pay_result);
	if ($pay_result instanceof \LSYS\PayGateway\Pay\PayResult\SuccResult){
		//支付完成回调
		echo "<pre>支付成功\n";
		echo "订单号:".$pay_result->getPaySn()."\n";
		echo "支付号:".$pay_result->getPayNo()."\n";
		if ($pay_result->getMoney()){
			echo "支付金额:".$pay_result->getMoney()->to(Money::CNY)."\n";
		}
		if ($pay_result->getSeller()){
			echo "支付用户:".$pay_result->getPayAccount()."\n";
		}
		echo "\n</pre>";
	}else if ($pay_result instanceof \LSYS\PayGateway\Pay\PayResult\FailResult){
		echo "<pre>支付失败"."\n";
		echo $pay_result->getMsg()."\n</pre>";
	}else{
		echo "<pre>"."\n";
		echo $pay_result->getMsg()."\n</pre>";
	}
}
function payNotify(PayAdapterNotify $pay,PayResult $pay_result){
    global $paylog;$paylog->add(get_class($pay), $pay_result);
    //后台回调,不能处理抛异常.
    if ($pay_result instanceof \LSYS\PayGateway\Pay\PayResult\SuccResult){
        $pay_result->getPayNo();
        $pay_result->getPaySn();
        //error... set false
        $status=true;
        $msg='';
        $pay->payNotifyOutput($status,$msg);
        //
    }else{
        if($pay_result instanceof \LSYS\PayGateway\Pay\PayResult\FailResult){
            $pay->payNotifyOutput(false,'sign fail');
        }else{
            $pay->payNotifyOutput(true);
        }
    }
}

function resultCallback($pay,PayResult $pay_result){
    global $paylog;$paylog->add(get_class($pay), $pay_result);
	//支付完成回调
	if ($pay_result instanceof \LSYS\PayGateway\Pay\PayResult\SuccResult){
		//支付完成回调
		echo "<pre>支付成功\n";
		echo "订单号:".$pay_result->getPaySn()."\n";
		echo "支付号:".$pay_result->getPayNo()."\n";
		if ($pay_result->getMoney()){
			echo "支付金额:".$pay_result->getMoney()->to(Money::CNY)."\n";
		}
		if ($pay_result->getSeller()){
			echo "支付用户:".$pay_result->getPayAccount()."\n";
		}
		echo "\n</pre>";
	}else if ($pay_result instanceof \LSYS\PayGateway\Pay\PayResult\FailResult){
		echo "<pre>支付失败"."\n";
		echo $pay_result->getMsg()."\n</pre>";
	}else{
		echo "<pre>"."\n";
		echo $pay_result->getMsg()."\n</pre>";
	}
}

//统一退款处理
function refundCallback($refund,RefundResult $refund_result){
    global $paylog;$paylog->add(get_class($refund), $refund_result);
	//支付完成回调
	if ($refund_result instanceof SuccResult){
		//支付完成回调
		echo "退款完成成功\n";
		echo "退款号:".$refund_result->getRefundNo()."\n";
		echo "支付方退款号:".$refund_result->getRefundPayNo()."\n";
		print_r($refund_result);
	}else if ($refund_result instanceof FailResult){
		echo "退款失败:";
		echo $refund_result->getMsg();//save to db...
	}else{
		echo "success";//其他
	}
}


//统一转账处理
function transfersCallback($transfers,TransfersResult $transfers_result){
	//支付完成回调
     global $paylog;
     $paylog->add(get_class($transfers), $transfers_result);
	if ($transfers_result instanceof \LSYS\PayGateway\Transfers\TransfersResult\SuccResult){
		//支付完成回调
		echo "转账完成\n";
		echo "转账号:".$transfers_result->getTransfersNo()."\n";
	}else if ($transfers_result instanceof \LSYS\PayGateway\Transfers\TransfersResult\FailResult){
		 //"退款失败"."\n";
		echo $transfers_result->getMsg();//save to db...
	}else{
		//other
	}
}

//统一转账处理
function billCallback($bill,Result $result){
	//get_class($bill);//根据不同的类进行不同处理
	print_r($result);//保存到数据库或其他;
}

