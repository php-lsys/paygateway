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
class AppleRefund implements  \LSYS\PayGateway\Pay\RefundAdapter, RefundNotify{
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
		
		$msg=$refund_param->getRefundMsg();
		$recharge_pay_no= $refund_param->getPayNo();
		$refund_money = intval($refund_param->getRefundPayMoney()*100);
		$total_money = intval($refund_param->getTotalPayMoney()*100);
		$return_no = $refund_param->getReturnNo();
		$notify_url = $this->_config->getNotifyUrl();
		$merid=$this->_config->getMerid();
		$msg  =  $refund_param->getRefundMsg();
		
		
		$sdk_sign_cert_path=$this->_config->getSignCertPath();
		$sdk_sign_cert_pwd=$this->_config->getSignCertPwd();
		$verify_cert_dir=$this->_config->getVerifyCertDir();
		
		if ($this->_config->getMode()=='sandbox'){
			require_once (__DIR__.'/../../../../../libs/upacp_apple_pay/sdk/SDKConfigDev.php');
		}else{
			require_once (__DIR__.'/../../../../../libs/upacp_apple_pay/sdk/SDKConfig.php');
		}
		require_once (__DIR__.'/../../../../../libs/upacp_apple_pay/sdk/acp_service.php');
		
		$params = array(
		
				//以下信息非特殊情况不需要改动
				'version' => '5.0.0',		      //版本号
				'encoding' => 'utf-8',		      //编码方式
				'signMethod' => '01',		      //签名方法
				'txnType' => '04',		          //交易类型
				'txnSubType' => '00',		      //交易子类
				'bizType' => '000201',		      //业务类型
				'accessType' => '0',		      //接入类型
				'channelType' => '07',		      //渠道类型
				'backUrl' => $notify_url, //后台通知地址
		
				//TODO 以下信息需要填写
				'orderId' => $return_no,	    //商户订单号，8-32位数字字母，不能含“-”或“_”，可以自行定制规则，重新产生，不同于原消费，此处默认取demo演示页面传递的参数
				'merId' => $merid,	        //商户代码，请改成自己的测试商户号，此处默认取demo演示页面传递的参数
				'origQryId' => $recharge_pay_no, //原消费的queryId，可以从查询接口或者通知接口中获取，此处默认取demo演示页面传递的参数
				'txnTime' => date("YmdHis"),	    //订单发送时间，格式为YYYYMMDDhhmmss，重新产生，不同于原消费，此处默认取demo演示页面传递的参数
				'txnAmt' => $refund_money,       //交易金额，退货总金额需要小于等于原消费
				// 		'reqReserved' =>'透传信息',            //请求方保留域，透传字段，查询、通知、对账文件中均会原样出现，如有需要请启用并修改自己希望透传的数据
		);
		
		\com\unionpay\acp\sdk\AcpService::sign ( $params ,$sdk_sign_cert_path,$sdk_sign_cert_pwd); // 签名
		$url = com\unionpay\acp\sdk\SDK_BACK_TRANS_URL;
		
		$result_arr = \com\unionpay\acp\sdk\AcpService::post ( $params, $url,$html);
		if(count($result_arr)<=0) { //没收到200应答的情况
			printResult ( $url, $params, "" );
			return;
		}
		
		if (!\com\unionpay\acp\sdk\AcpService::validate ($result_arr,$verify_cert_dir) ){
		    return (new \LSYS\PayGateway\Pay\RefundResult\FailResult($html,'sign is fail'))->setParam($result_arr)->setSignFail();
		}
		if ($result_arr["respCode"] == "00"){
			//交易已受理，等待接收后台通知更新订单状态，如果通知长时间未收到也可发起交易状态查询
			$pay_no=$result_arr[ 'queryId'];
			$out_trade_no=$result_arr[ 'orderId'];
			return (new \LSYS\PayGateway\Pay\RefundResult\SuccResult($html,$out_trade_no,$pay_no))->setParam($result_arr);
		} else if ($result_arr["respCode"] == "03"
				|| $result_arr["respCode"] == "04"
				|| $result_arr["respCode"] == "05" ){
					$pay_no=@$result_arr[ 'queryId'];
					return (new \LSYS\PayGateway\Pay\RefundResult\IngResult($html,$return_no,$pay_no))->setParam($result_arr);
		} else {
		    return (new \LSYS\PayGateway\Pay\RefundResult\FailResult($html,@$result_arr['respMsg'],$return_no))->setParam($result_arr);
		}
	}
	public function refundNotify(){
		ignore_user_abort(true);
		if ($this->_config->getMode()=='sandbox'){
			require_once (__DIR__.'/../../../../../libs/upacp_apple_pay/sdk/SDKConfigDev.php');
		}else{
			require_once (__DIR__.'/../../../../../libs/upacp_apple_pay/sdk/SDKConfig.php');
		}
		
		require_once (__DIR__.'/../../../../../libs/upacp_apple_pay/sdk/acp_service.php');
		$verify_cert_dir=$this->_config->getVerifyCertDir();
		if (!isset ( $_POST ['signature'] )) die("fail");
		if(\com\unionpay\acp\sdk\AcpService::validate ( $_POST ,$verify_cert_dir)){
		    return (new \LSYS\PayGateway\Pay\RefundResult\FailResult($_POST,'sign is fail'))->setParam($_POST)->setSignFail();
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
					@$trade_no=$_POST[ 'queryId'];
					$result=  new \LSYS\PayGateway\Pay\RefundResult\IngResult($_POST,$out_trade_no,$trade_no);
					$result->setParam($_POST);
		} else {
		    $result=  new \LSYS\PayGateway\Pay\RefundResult\FailResult($_POST,$_POST["respMsg"],$return_no);
		    $result->setParam($_POST);
		}
		return $result;
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