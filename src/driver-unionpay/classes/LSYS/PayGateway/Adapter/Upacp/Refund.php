<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Upacp;
use LSYS\PayGateway\Pay\RefundParam;
use LSYS\PayGateway\Pay\RefundResult;
use LSYS\PayGateway\Pay\RefundNotify;
class Refund implements  \LSYS\PayGateway\Pay\RefundAdapter, RefundNotify{
	/**
	 * @var RefundConfig
	 */
	protected $_config;
	public function __construct(RefundConfig $config){
		$this->_config=$config;
	}
	/**
	 * refund money
	 * @param RefundParam $refund_param
	 * @return RefundResult
	 */
	public function refund(RefundParam $refund_param){
		
		if ($this->_config->getMode()=='sandbox'){
			require_once (__DIR__.'/../../../../../libs/upacp_sdk_php/utf8/func/SDKConfigDev.php');
		}else{
			require_once (__DIR__.'/../../../../../libs/upacp_sdk_php/utf8/func/SDKConfig.php');
		}
		
		require_once (__DIR__.'/../../../../../libs/upacp_sdk_php/utf8/func/common.php');
		require_once (__DIR__.'/../../../../../libs/upacp_sdk_php/utf8/func/secureUtil.php');
		require_once (__DIR__.'/../../../../../libs/upacp_sdk_php/utf8/func/httpClient.php');
		require_once (__DIR__.'/../../../../../libs/upacp_sdk_php/utf8/func/log.class.php');
		
		
		
		
		$msg=$refund_param->getRefundMsg();
		$recharge_pay_no= $refund_param->getPayNo();
		$refund_money = intval($refund_param->getRefundPayMoney()*100);
		$total_money = intval($refund_param->getTotalPayMoney()*100);
		$return_no = $refund_param->getReturnNo();
		$notify_url = $this->_config->getNotifyUrl();
		$merid=$this->_config->getMerid();
		$sdk_sign_cert_path=$this->_config->getSignCertPath();
		$sdk_sign_cert_pwd=$this->_config->getSignCertPwd();
		$msg  =  $refund_param->getRefundMsg();
		if (empty($msg))$msg='退款';
		$params = array(
				'version' => '5.0.0',		//版本号
				'encoding' => 'utf-8',		//编码方式
				'certId' =>getCertId ( $sdk_sign_cert_path,$sdk_sign_cert_pwd ),			//证书ID
				'signMethod' => '01',		//签名方法
				'txnType' => '04',		//交易类型
				'txnSubType' => '00',		//交易子类
				'bizType' => '000201',		//业务类型
				'accessType' => '0',		//接入类型
				'channelType' => '07',		//渠道类型
				'orderId' => $return_no,	//商户订单号，重新产生，不同于原消费
				'merId' => $merid,	//商户代码，请修改为自己的商户号
				'origQryId' => $recharge_pay_no,    //原消费的queryId，可以从查询接口或者通知接口中获取
				'txnTime' => date('YmdHis'),	//订单发送时间，重新产生，不同于原消费
				'txnAmt' => $refund_money,              //交易金额，退货总金额需要小于等于原消费
				'backUrl' => $notify_url,	   //后台通知地址
				'reqReserved' =>$msg, //请求方保留域，透传字段，查询、通知、对账文件中均会原样出现
		);
		// 签名
		sign ( $params ,$sdk_sign_cert_path);
		
		$result = sendHttpRequest ( $params, SDK_BACK_TRANS_URL );
	
		//返回结果展示
		$result_arr = coverStringToArray ( $result );
		
		
		$verify_cert_dir=$this->_config->getVerifyCertDir();
		
		if(!verify ( $result_arr ,$verify_cert_dir)){
		    return (new \LSYS\PayGateway\Pay\RefundResult\FailResult($result,'sign is fail'))->setSignFail()->setParam($result_arr);
		}
		if ($result_arr["respCode"] == "00"){
			$out_trade_no=$result_arr[ 'orderId'];
			$trade_no=$result_arr[ 'queryId'];
			return (new \LSYS\PayGateway\Pay\RefundResult\SuccResult($result,$out_trade_no,$trade_no))->setParam($result_arr);
		} else if ($result_arr["respCode"] == "03"
				|| $result_arr["respCode"] == "04"
				|| $result_arr["respCode"] == "05" ){
				$out_trade_no=$result_arr[ 'orderId'];
				$trade_no=$result_arr[ 'queryId'];
				return (new \LSYS\PayGateway\Pay\RefundResult\IngResult($result,$out_trade_no,$trade_no))->setParam($result_arr);
		} else {
		    return (new \LSYS\PayGateway\Pay\RefundResult\FailResult($result,$result_arr["respMsg"],$return_no))->setParam($result_arr);
		}
	}
	public function refundNotify(){
		ignore_user_abort(true);
		
		if ($this->_config->getMode()=='sandbox'){
			require_once (__DIR__.'/../../../../../libs/upacp_sdk_php/utf8/func/SDKConfigDev.php');
		}else{
			require_once (__DIR__.'/../../../../../libs/upacp_sdk_php/utf8/func/SDKConfig.php');
		}
		
		require_once (__DIR__.'/../../../../../libs/upacp_sdk_php/utf8/func/common.php');
		require_once (__DIR__.'/../../../../../libs/upacp_sdk_php/utf8/func/secureUtil.php');
		if(!isset($_POST ['signature']))die("fail");
		
		
		$verify_cert_dir=$this->_config->getVerifyCertDir();
		if(!verify ( $_POST ,$verify_cert_dir)){
		    return  (new \LSYS\PayGateway\Pay\RefundResult\FailResult($_POST,'sign is fail'))->setParam($_POST)->setSignFail();
		}

		//Loger::instance(Loger::TYPE_REFUND)->add($_POST);
		
		
		if ($_POST["respCode"] == "00"){
			$out_trade_no=$_POST[ 'orderId'];
			$trade_no=$_POST[ 'queryId'];
			$result= new \LSYS\PayGateway\Pay\RefundResult\SuccResult($_POST,$out_trade_no,$trade_no);
			$result->setParam($_POST);
		} else if ($_POST["respCode"] == "03"
				|| $_POST["respCode"] == "04"
				|| $_POST["respCode"] == "05" ){
					$out_trade_no=$_POST[ 'orderId'];
					$trade_no=$_POST[ 'queryId'];
					$result=  new \LSYS\PayGateway\Pay\RefundResult\IngResult($_POST,$out_trade_no,$trade_no);
					$result->setParam($_POST);
		} else {
		    $result=  new \LSYS\PayGateway\Pay\RefundResult\FailResult($_POST,$_POST["respMsg"],$return_no);
		    $result->setParam($_POST);
		}
		return  $result;
	}
	
	public function refundNotifyOutput($status=true,$msg=null){
		if ($status){
			http_response_code(200);
			die("success");
		}else{
			http_response_code(500);
			die($msg);
		}
	}
	
}