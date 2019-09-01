<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Qpay;

use LSYS\PayGateway\Pay\Query;
use LSYS\PayGateway\Pay\QueryParam;
use LSYS\PayGateway\Pay\PayAdapterNotify;

abstract class PayNotify implements \LSYS\PayGateway\Pay\PayAdapterSimple, Query,PayAdapterNotify{
	protected $_config;
	public function __construct(Config $config){
		$this->_config=$config;
	}
	public function payNotify(){
		//@todo...
		$xml=file_get_contents("php://input");
		try {
			$result=Tools::parse($result);
		} catch (\Exception $e){
		    return  (new \LSYS\PayGateway\Pay\PayResult\FailResult(null,$e->getMessage()))->setLocalFail();
		}
		//Loger::instance(Loger::TYPE_PAY_NOTIFY)->add($this->supportName(),$xml);
		if (!isset($result["out_trade_no"])
			||!isset($result["transaction_id"])
			||!isset($result["total_fee"])
			||!isset($result["openid"])
		){
		    return  (new \LSYS\PayGateway\Pay\PayResult\FailResult(null,$xml))->setLocalFail();
		}
		$out_trade_no=$result["out_trade_no"];
		$trade_no=$result["transaction_id"];
		$total_fee=round($result["total_fee"]/100,2);
		$openid=$result["openid"];
		if (isset($result['return_code'])&&$result['return_code']=='SUCCESS'
			&&isset($result['result_code'])&&$result['result_code']=='SUCCESS'){
			$result=new \LSYS\PayGateway\Pay\PayResult\SuccResult(null,$out_trade_no,$trade_no,$result);
			$result->setMoney($total_fee)->setPayAccount($openid);
			return $result;
		}
		return  new \LSYS\PayGateway\Pay\PayResult\IngResult(null,$out_trade_no,$result,$openid);
	
	}
	//out to wechat pay
	public function payNotifyOutput($status=true,$msg='OK'){
		die(Tools::toXml(array(
			'return_code'=>$status?'SUCCESS':"FAIL",
			'return_msg'=>strip_tags($msg)
		)));
	}
	public function query(QueryParam $param){
		$out_trade_no = $param->getPaySn();
		$tid=$param->getPayNo();
		$param=array();
		$tid&&$param['transaction_id']= $tid;
		$param['out_trade_no']=$out_trade_no;
		$xml=Tools::getToXml($param, $this->_config);
		$url="https://qpay.qq.com/cgi-bin/pay/qpay_order_query.cgi";
		$result=Tools::post($url, $xml, $this->_config);
		$_result=Tools::parse($result);
		//vars
		if(array_key_exists("return_code", $_result)
				&& array_key_exists("result_code", $_result)
				&& $_result["return_code"] == "SUCCESS"
				&& $_result["result_code"] == "SUCCESS")
		{
			switch ($_result["trade_state"]){
				case 'SUCCESS':
				    return (new \LSYS\PayGateway\Pay\PayResult\SuccResult($result,$out_trade_no, $_result['transaction_id']))->setParam($_result);
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
		return (new \LSYS\PayGateway\Pay\PayResult\FailResult($result,$_result["return_msg"]))->setLocalFail()->setParam($_result);
	}
	
}