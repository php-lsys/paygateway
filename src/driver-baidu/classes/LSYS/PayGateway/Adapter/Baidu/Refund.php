<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Baidu;
use LSYS\PayGateway\Pay\RefundNotify;
use LSYS\PayGateway\Pay\RefundParam;
use LSYS\PayGateway\Pay\RefundResult;
class Refund implements  \LSYS\PayGateway\Pay\RefundAdapter, RefundNotify{
	/**
	 * @var RefundConfig
	 */
	protected $_config;
	/**
	 * @var \bdpay_sdk
	 */
	protected $_pay_sdk;
	public function __construct(RefundConfig $config){
		$this->_config=$config;
	}
	/**
	 * refund money
	 * @param RefundParam $refund_param
	 * @return RefundResult
	 */
	public function refund(RefundParam $refund_param){
		
		$notify_url=$this->_config->getNotifyUrl();
		$msg=$refund_param->getRefundMsg();
		$recharge_pay_no= $refund_param->getPayNo();
		$refund_money = intval($refund_param->getRefundPayMoney()*100);
		$total_money = intval($refund_param->getTotalPayMoney()*100);
		$return_no = $refund_param->getReturnNo();
		
		
		require_once (__DIR__."/../../../../../libs/bdpayrefundphp/bdpay_sdk.php");
		require_once (__DIR__."/../../../../../libs/bdpayrefundphp/bdpay_refund.cfg.php");
		\sp_conf::$config=array(
				'sp_no'=>$this->_config->getSpNo(),
				'key_file'=>$this->_config->getKeyFile()
		);
		
		$bdpay_sdk = new \bdpay_sdk();
		/*
		 *refund.html页面获取的参数
		 */
		
		$output_type =1;
		$output_charset = 1;
		$return_url = $notify_url;
		$sp_refund_no = $return_no;
		$order_no = $recharge_pay_no;
		$return_method= 1;
		$cashback_amount = $refund_money;
		$cashback_time= date("YmdHis");
		
		// 用于测试的商户请求退款接口的表单参数，具体的表单参数各项的定义和取值参见接口文档
		$params = array (
				'service_code' => \sp_conf::BFB_REFUND_INTERFACE_SERVICE_ID,
				'input_charset' => \sp_conf::BFB_INTERFACE_ENCODING,
				'sign_method' => \sp_conf::SIGN_METHOD_MD5,
				'output_type' => $output_type,
				'output_charset' => $output_charset,
				'return_url' => $return_url,
				'return_method' => $return_method,
				'version' =>  \sp_conf::BFB_INTERFACE_VERSION,
		    'sp_no' => \sp_conf::SP_NO(),
				'order_no'=>$order_no,
				'cashback_amount' => $cashback_amount,
				'cashback_time' => $cashback_time,
				'currency' => \sp_conf::BFB_INTERFACE_CURRENTCY,
				'sp_refund_no' => $sp_refund_no
		);
		
		$refund_url = $bdpay_sdk->create_baifubao_Refund_url($params, \sp_conf::BFB_REFUND_URL);
		
		$retry = 0;
		while (empty($content) && $retry < \sp_conf::BFB_QUERY_RETRY_TIME) {
			$content = $bdpay_sdk->request($refund_url);
			$retry++;
		}
		if (empty($content)) {
		    return new \LSYS\PayGateway\Pay\RefundResult\FailResult($content, 'call baidu api fail',$return_no);
		}
		$response_arr = json_decode(
				json_encode(simplexml_load_string($content)), true);
		// 上句解析xml文件时，如果某字段没有取值时，会被解析成一个空的数组，对于没有取值的情况，都默认设为空字符串
		foreach ($response_arr as &$value) {
			if (empty($value) && is_array($value)) {
				$value = '';
			}
		}
		unset($value);
		return (new \LSYS\PayGateway\Pay\RefundResult\IngResult($content,$return_no,@$response_arr['sp_no']))->setParam($response_arr);
	}
	
	public function refundNotify(){
		ignore_user_abort(true);
		
		require_once (__DIR__."/../../../../../libs/bdpayrefundphp/bdpay_sdk.php");
		require_once (__DIR__."/../../../../../libs/bdpayrefundphp/bdpay_refund.cfg.php");
		\sp_conf::$config=array(
			'sp_no'=>$this->_config->getSpNo(),
			'key_file'=>$this->_config->getKeyFile()
		);
		
		$this->_pay_sdk=$bdpay_sdk = new \bdpay_sdk();
		
		if (false === $bdpay_sdk->check_bfb_refund_result_notify()) {
		    return  (new \LSYS\PayGateway\Pay\RefundResult\FailResult($_GET,'sign is fail'))->setSignFail()->setParam($_GET);
		}
		
		////Loger::instance(Loger::TYPE_REFUND)->add($_POST);
		
// 		bfb_order_no 2014081290001000051110157533474 百度钱包交易号
// 		cashback_amount 1 退款金额，以分为单位
// 		order_no 20140814170227451966 外部商户交易号
// 		ret_code 1 退款结果
// 		ret_detail 退款详情为空
// 		sp_no 9000100005 外部商户号
// 		sp_refund_no 201408141703270 外部商户退款流水号
// 		sign 0e423b4d2cc13767b74e19287dedc650 签名结果
// 		sign_method
		
		$batch_no=@$_GET['sp_refund_no'];
		$dbref=@$_GET['bfb_order_no'];
		
		$result=new \LSYS\PayGateway\Pay\RefundResult\SuccResult($_GET,$batch_no,$dbref);
		$result->setParam($_GET);
		return  $result;
	}
	
	/**
	 * pay notify
	 */
	public function refundNotifyOutput($status=true,$msg=null){
		if($this->_pay_sdk)die('fail');
		if ($status){
			$this->_pay_sdk->notify_bfb();
			die();
		}else{
			http_response_code(500);
			die($msg);
		}
	}
	
}