<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Wechat;
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
		require_once (__DIR__."/../../../../../libs/wechat/lib/WxPay.Api.php");
		\WxPayApi::$config=$this->_config->getWxPayConfigObj();
		
		$recharge_pay_no= $refund_param->getPayNo();
		$refund_money = intval($refund_param->getRefundPayMoney()*100);
		$total_money = intval($refund_param->getTotalPayMoney()*100);
		$return_no = $refund_param->getReturnNo();

		$transaction_id =$recharge_pay_no;
		$total_fee = $total_money;
		$refund_fee = $refund_money;
		$input = new \WxPayRefund();
		$input->SetTransaction_id($transaction_id);
		$input->SetTotal_fee($total_fee);
		$input->SetRefund_fee($refund_fee);
		$input->SetOut_refund_no($return_no);
		$input->SetOp_user_id($this->_config->get_WxPayConfigObj()->MCHID);
		try{
		    $result=\WxPayApi::refund($input,8,$response);
		}catch (\WxPayException $e){
		    return (new \LSYS\PayGateway\Pay\RefundResult\FailResult($response, $e->getMessage(),$return_no))->setParam($result)->setLocalFail();
		}
		if ($result["return_code"] != "SUCCESS"){
		    return (new \LSYS\PayGateway\Pay\RefundResult\FailResult($response,$result['return_msg'],$return_no))->setParam($result);
		}
		if($result["result_code"]!='SUCCESS'){
		    return (new \LSYS\PayGateway\Pay\RefundResult\FailResult($response,$result['err_code_des'],$return_no))->setParam($result)->setLocalRollback();
		}
		return (new \LSYS\PayGateway\Pay\RefundResult\SuccResult($response,$return_no,$result['refund_id']))->setParam($result);
	}
}