<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Qpay;
use LSYS\PayGateway\Pay\RefundParam;
class Refund implements  \LSYS\PayGateway\Pay\RefundAdapter{
	/**
	 * @var Config
	 */
	protected $_config;
	public function __construct(Config $config){
		$this->_config=$config;
	}
	public function refund(RefundParam $refund_param){
		$recharge_pay_no= $refund_param->getPayNo();
		$refund_money = intval($refund_param->getRefundPayMoney()*100);
		$total_money = intval($refund_param->getTotalPayMoney()*100);
		$return_no = $refund_param->getReturnNo();
		$param=array();
		$param['transaction_id']=$recharge_pay_no;
		$param['total_fee']=$total_money;
		$param['refund_fee']=$refund_money;
		$param['out_refund_no']=$return_no;
		$xml=Tools::getToXml($param, $this->_config);
		$url="https://api.qpay.qq.com/cgi-bin/pay/qpay_refund.cgi";
		try{
			$result_=Tools::post($url, $xml, $this->_config);
			$result=Tools::parse($result_);
		}catch (\Exception $e){
		    return new \LSYS\PayGateway\Pay\RefundResult\FailResult($e->getTraceAsString(),$e->getMessage(),$return_no);
		}
		if($result["result_code"]!='SUCCESS'){
		    return (new \LSYS\PayGateway\Pay\RefundResult\FailResult($result_,$result['err_code_des'],$return_no))->setParam($result)->setLocalRollback();
		}
		return (new \LSYS\PayGateway\Pay\RefundResult\SuccResult($result_,$return_no,$result['refund_id']))->setParam($result);
	}
}