<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Wechat;
use LSYS\PayGateway\Pay\Query;
use LSYS\PayGateway\Pay\QueryParam;
use LSYS\PayGateway\Pay\PayAdapterNotify;
use LSYS\PayGateway\Pay\Reverse;
use LSYS\PayGateway\Pay\ReverseParam;
abstract class PayNotify implements \LSYS\PayGateway\Pay\PayAdapterSimple, Query,Reverse,PayAdapterNotify{
	protected $_config;
	public function __construct(Config $config){
		$this->_config=$config;
	}
	public function payNotify(){
		require_once (__DIR__."/../../../../../libs/wechat/lib/WxPay.Api.php");
		\WxPayApi::$config=$this->_config->getWxPayConfigObj();
		$xml=file_get_contents("php://input");
		try {
			$result = \WxPayResults::Init($xml);
		} catch (\WxPayException $e){
		    if ($e->getCode()===888) return (new \LSYS\PayGateway\Pay\PayResult\FailResult($xml,'sign is fail'))->setSignFail();
			return  (new \LSYS\PayGateway\Pay\PayResult\FailResult($xml,$e->getMessage()))->setLocalFail();
		}
		////Loger::instance(Loger::TYPE_PAY_NOTIFY)->add($this->supportName(),$xml);
		if (!isset($result["out_trade_no"])
			||!isset($result["transaction_id"])
			||!isset($result["total_fee"])
			||!isset($result["openid"])
		){
		    return  (new \LSYS\PayGateway\Pay\PayResult\FailResult($xml,strip_tags($xml)))->setParam($result)->setLocalFail();
		}
		$out_trade_no=$result["out_trade_no"];
		$trade_no=$result["transaction_id"];
		$total_fee=round($result["total_fee"]/100,2);
		$openid=$result["openid"];
		if (isset($result['return_code'])&&$result['return_code']=='SUCCESS'
			&&isset($result['result_code'])&&$result['result_code']=='SUCCESS'){
			    $result=new \LSYS\PayGateway\Pay\PayResult\SuccResult($xml,$out_trade_no,$trade_no);
			    $result->setMoney($total_fee)->setPayAccount($openid)->setParam($result);
			return $result;
		}
		return  (new \LSYS\PayGateway\Pay\PayResult\IngResult($xml,$out_trade_no,$trade_no))
		  ->setPayAccount($openid)
		  ->setMoney($total_fee)
		  ->setParam($result);
// 		try{
// 			$input = new \WxPayOrderQuery();
// 			$input->SetTransaction_id($trade_no);
// 			$_result = \WxPayApi::orderQuery($input);
// 		}catch (\WxPayException $e){
// 			//Loger::instance(Loger::TYPE_PAY_NOTIFY)->add($this->supportName(),$e);
// 			return  (new \LSYS\PayGateway\Pay\PayResult\FailResult(null,$e->getMessage()))->setLocalFail();
// 		}
// 		if(array_key_exists("return_code", $_result)
// 			&& array_key_exists("result_code", $_result)
// 			&& $_result["return_code"] == "SUCCESS"
// 			&& $_result["result_code"] == "SUCCESS")
// 		{
// 			$result=new \LSYS\PayGateway\Pay\PayResult\SuccResult(null,$out_trade_no,$trade_no,$result);
// 			$result->setMoney($total_fee)->setPayAccount($openid);
// 			return $result; 
// 		}
// 		return  new \LSYS\PayGateway\Pay\PayResult\IngResult(null,$out_trade_no,$result,$openid);
	}
	//out to wechat pay
	public function payNotifyOutput($status=true,$msg='OK'){
		$return = new \WxPayNotifyReply();
		$return->SetReturn_code($status?'SUCCESS':"FAIL");
		$return->SetReturn_msg($msg);
		\WxpayApi::replyNotify($return->ToXml());
		exit;
	}
	public function query(QueryParam $param){
		require_once (__DIR__."/../../../../../libs/wechat/lib/WxPay.Api.php");
		\WxPayApi::$config=$this->_config->getWxPayConfigObj();
		$out_trade_no = $param->getPaySn();
		$input = new \WxPayOrderQuery();
		$input->SetOut_trade_no($out_trade_no);
		try{
			$_result = \WxPayApi::orderQuery($input,8,$result);
		}catch (\WxPayException $e){
		    return (new \LSYS\PayGateway\Pay\PayResult\FailResult($result,$e->getMessage()))->setLocalFail();
		}
		if(array_key_exists("return_code", $_result)
				&& array_key_exists("result_code", $_result)
				&& $_result["return_code"] == "SUCCESS"
				&& $_result["result_code"] == "SUCCESS")
		{
			switch ($_result["trade_state"]){
				case 'SUCCESS':
				    return (new \LSYS\PayGateway\Pay\PayResult\SuccResult($result,$out_trade_no,$_result['transaction_id']))->setParam($_result);
				break;
				case 'USERPAYING':
				    return (new \LSYS\PayGateway\Pay\PayResult\IngResult($result,$out_trade_no,@$_result['transaction_id']))->setParam($_result);
				break;
				case 'NOTPAY':
				case 'REVOKED':
				case 'CLOSED':
				case 'PAYERROR':
				    return (new \LSYS\PayGateway\Pay\PayResult\FailResult($result,$_result["trade_state_desc"],$out_trade_no, @$_result['transaction_id']))->setParam($_result);
				break;
			}
		}
		return (new \LSYS\PayGateway\Pay\PayResult\FailResult($result,$_result["return_msg"]))->setLocalFail();
	}
	public function reverse(ReverseParam $param){
		require_once (__DIR__."/../../../../../libs/wechat/lib/WxPay.Api.php");
		\WxPayApi::$config=$this->_config->getWxPayConfigObj();
		$input = new \WxPayReverse();
		$input->SetOut_trade_no($param->get_pay_sn());
		$input->SetTransaction_id($param->get_pay_no());
		try{
			$_result = \WxPayApi::reverse($input,10,$result);
		}catch (\WxPayException $e){
		    return (new \LSYS\PayGateway\Pay\ReverseResult\FailResult($result,$e->getMessage()))->setLocalFail();
		}
		if(array_key_exists("return_code", $_result)
				&& array_key_exists("result_code", $_result)
				&& $_result["return_code"] == "SUCCESS"
				&& $_result["result_code"] == "SUCCESS")
		{
			if ($_result["recall"]=='N'){
			    return (new \LSYS\PayGateway\Pay\ReverseResult\SuccResult($result,$param->getPaySn(),$param->getPayNo()))->setParam($_result);
			}else{
				switch ($_result['err_code']){
					case 'SYSTEMERROR':
					    return (new \LSYS\PayGateway\Pay\ReverseResult\IngResult($result,$param->getPaySn(),$param->getPayNo()))->setParam($_result);
					break;
					case 'REVERSE_EXPIRE':
					    return (new \LSYS\PayGateway\Pay\ReverseResult\SuccResult($result,$param->getPaySn(),$param->getPayNo()))->setParam($_result);
					break;
					default:
					    return (new \LSYS\PayGateway\Pay\ReverseResult\FailResult($result,$_result['err_code_des']))->setParam($_result);
				}
			}
		}
		return (new \LSYS\PayGateway\Pay\ReverseResult\FailResult($result,$_result['return_msg']))->setParam($_result)->setLocalFail();
	}
	
}