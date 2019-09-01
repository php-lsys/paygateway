<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Upacp;
use LSYS\PayGateway\Exception;
use LSYS\PayGateway\Pay\PayParam;
use LSYS\PayGateway\Pay\PayRender;
use LSYS\PayGateway\Pay\Query;
use LSYS\PayGateway\Pay\QueryParam;
use LSYS\PayGateway\Pay\PayAdapterNotify;
class ApplePay implements \LSYS\PayGateway\Pay\PayAdapterSimple, Query,PayAdapterNotify{
	/**
	 * @var PayConfig
	 */
	protected $_config;
	public function __construct(PayConfig $config){
		$this->_config=$config;
	}
	public static function supportType($type){
	    return $type&self::TYPE_IOS;
		//|self::TYPE_WAP 未开发网页接口...
	}
	public function payRender(PayParam $pay_param){
		$notify_url=$this->_config->getNotifyUrl();
		$merid=$this->_config->getMerid();
		$sdk_sign_cert_path=$this->_config->getSignCertPath();
		$sdk_sign_cert_pwd=$this->_config->getSignCertPwd();
		$verify_cert_dir=$this->_config->getVerifyCertDir();
		
		
		$show_url=$pay_param->getShowUrl();
		$out_trade_no=$pay_param->getSn();
		$total_fee=intval($pay_param->getPayMoney()*100);
		$subject=$pay_param->getTitle();
		$body=$pay_param->getBody();
		
		$timeout=$pay_param->getTimeout();
		$timeout||$timeout=time()+3600*24*7;
		$timeout=date("YmdHis",$timeout);
		
		$ctime=$pay_param->getCreateTime();
		
		if ($this->_config->getMode()=='sandbox'){
			require_once (__DIR__.'/../../../../../libs/upacp_apple_pay/sdk/SDKConfigDev.php');
		}else{
			require_once (__DIR__.'/../../../../../libs/upacp_apple_pay/sdk/SDKConfig.php');
		}
		
		require_once (__DIR__.'/../../../../../libs/upacp_apple_pay/sdk/acp_service.php');

		
		$params = array(
				//以下信息非特殊情况不需要改动
				'version' => '5.0.0',                 //版本号
				'encoding' => 'utf-8',				  //编码方式
				'txnType' => '01',				      //交易类型
				'txnSubType' => '01',				  //交易子类
				'bizType' => '000201',				  //业务类型
				'backUrl' =>$notify_url,	  //后台通知地址
				'signMethod' => '01',	              //签名方法
				'channelType' => '08',	              //渠道类型，07-PC，08-手机
				'accessType' => '0',		          //接入类型
				'currencyCode' => '156',	          //交易币种，境内商户固定156
				'merId' => $merid,		//商户代码，请改自己的测试商户号，此处默认取demo演示页面传递的参数
				'orderId' => $out_trade_no,	//商户订单号，8-32位数字字母，不能含“-”或“_”，此处默认取demo演示页面传递的参数，可以自行定制规则
				'txnTime' => date("YmdHis",$ctime),	//订单发送时间，格式为YYYYMMDDhhmmss，取北京时间，此处默认取demo演示页面传递的参数
				'txnAmt' => $total_fee,	//交易金额，单位分，此处默认取demo演示页面传递的参数
				'orderDesc' =>$subject,       
				'payTimeout' =>date("YmdHis",$timeout),  
				//TODO 其他特殊用法请查看 pages/api_05_app/special_use_purchase.php
		);
		\com\unionpay\acp\sdk\AcpService::sign ( $params,$sdk_sign_cert_path, $sdk_sign_cert_pwd); // 签名
		$url = \com\unionpay\acp\sdk\SDK_App_Request_Url;
		
		$result_arr = \com\unionpay\acp\sdk\AcpService::post ($params,$url);
		if(count($result_arr)<=1) { //没收到200应答的情况
			$msg=key($result_arr);
			if (empty($msg))$msg='request url fail:'.$url;
			throw new Exception($msg);
		}
		if (!\com\unionpay\acp\sdk\AcpService::validate ($result_arr,$verify_cert_dir) ){
			throw new Exception('sign fail');
		}
		if ($result_arr["respCode"] != "00"){
			throw new Exception($result_arr["respMsg"],$result_arr["respCode"] );
		}
		$vars=array(
			'tn'=>$result_arr["tn"],
			'sn'=>$out_trade_no,
			'money'=>$pay_param->getPayMoney(),
			'subject'=>$subject
		);
		return new PayRender(PayRender::OUT_VARS, $vars);
	}
	protected function _verify(){
		
		if ($this->_config->getMode()=='sandbox'){
			require_once (__DIR__.'/../../../../../libs/upacp_apple_pay/sdk/SDKConfigDev.php');
		}else{
			require_once (__DIR__.'/../../../../../libs/upacp_apple_pay/sdk/SDKConfig.php');
		}
		
		require_once (__DIR__.'/../../../../../libs/upacp_apple_pay/sdk/acp_service.php');
		
		
		
		if (isset ( $_POST ['signature'] )) {
			$verify_cert_dir=$this->_config->getVerifyCertDir();
			return \com\unionpay\acp\sdk\AcpService::validate ( $_POST,$verify_cert_dir );
		}
		return false;
	}
	public function payNotify(){
		ignore_user_abort(true);
		if(!$this->_verify()){
		    return  (new \LSYS\PayGateway\Pay\PayResult\FailResult($_POST,'sign is fail'))->setSignFail();
		}
		//Loger::instance(Loger::TYPE_PAY_NOTIFY)->add($this->supportName(),$_POST);
		if(@$_POST['respCode']!='00'){
		    return  (new \LSYS\PayGateway\Pay\PayResult\FailResult($_POST,@$_POST['respMsg']))->setLocalFail();
		}
		$out_trade_no=$_POST[ 'orderId'];
		$trade_no=$_POST[ 'queryId'];
		$accNo=$_POST[ 'accNo'];
		$money=$_POST[ 'txnAmt'];
		$money=$money/100;
		$result=new \LSYS\PayGateway\Pay\PayResult\SuccResult($_POST,$out_trade_no,$trade_no);
		$result->setParam($_POST)->setMoney($money)->setPayAccount($accNo);
		return  $result;
	}

	public function payNotifyOutput($status=true,$msg=null){
		if ($status){
			http_response_code(200);
			die("success");		//请不要修改或删除
		}else{
			http_response_code(500);
			echo "fail";
			die();
		}
	}
	
	
	public function query(QueryParam $param){
		$merid=$this->_config->getMerid();
		$sdk_sign_cert_path=$this->_config->getSignCertPath();
		$sdk_sign_cert_pwd=$this->_config->getSignCertPwd();
		$verify_cert_dir=$this->_config->getVerifyCertDir();
		
		
		if ($this->_config->getMode()=='sandbox'){
			require_once (__DIR__.'/../../../../../libs/upacp_apple_pay/sdk/SDKConfigDev.php');
		}else{
			require_once (__DIR__.'/../../../../../libs/upacp_apple_pay/sdk/SDKConfig.php');
		}
		
		require_once (__DIR__.'/../../../../../libs/upacp_apple_pay/sdk/acp_service.php');
		
		
		
		
		$ctime=$param->getCreateTime();
		$pay_sn=$param->getPaySn();
		
		$params = array(
				//以下信息非特殊情况不需要改动
				'version' => '5.0.0',		  //版本号
				'encoding' => 'utf-8',		  //编码方式
				'signMethod' => '01',		  //签名方法
				'txnType' => '00',		      //交易类型
				'txnSubType' => '00',		  //交易子类
				'bizType' => '000000',		  //业务类型
				'accessType' => '0',		  //接入类型
				'channelType' => '07',		  //渠道类型
				'orderId' => $pay_sn,	//请修改被查询的交易的订单号，8-32位数字字母，不能含“-”或“_”，此处默认取demo演示页面传递的参数
				'merId' => $merid,	    //商户代码，请改自己的测试商户号，此处默认取demo演示页面传递的参数
				'txnTime' => $ctime,	//请修改被查询的交易的订单发送时间，格式为YYYYMMDDhhmmss，此处默认取demo演示页面传递的参数
		);
		
		\com\unionpay\acp\sdk\AcpService::sign ( $params,$sdk_sign_cert_path, $sdk_sign_cert_pwd ); // 签名
		$url = \com\unionpay\acp\sdk\SDK_SINGLE_QUERY_URL;
		
		$result_arr = \com\unionpay\acp\sdk\AcpService::post ( $params, $url,$html);
		if(count($result_arr)<=0) { //没收到200应答的情况
		    return (new \LSYS\PayGateway\Pay\PayResult\FailResult($html,"can't connect server"))->setLocalFail();
		}
		if (!\com\unionpay\acp\sdk\AcpService::validate ($result_arr,$verify_cert_dir) ){
		    return (new \LSYS\PayGateway\Pay\PayResult\FailResult($html,'sign is fail'))->setSignFail();
		}
		if ($result_arr["respCode"] == "00"){
			if ($result_arr["origRespCode"] == "00"){
				$out_trade_no=$result_arr[ 'orderId'];
				$trade_no=$result_arr[ 'queryId'];
				return (new \LSYS\PayGateway\Pay\PayResult\SuccResult($html,$out_trade_no, $trade_no))->setParam($result_arr);
			} else if ($result_arr["origRespCode"] == "03"
					|| $result_arr["origRespCode"] == "04"
					|| $result_arr["origRespCode"] == "05"){
						$out_trade_no=$result_arr[ 'orderId'];
						$trade_no=@$result_arr[ 'queryId'];
						return (new \LSYS\PayGateway\Pay\PayResult\IngResult($html,$out_trade_no,$trade_no))->setParam($result_arr);
			} else {
				$out_trade_no=$result_arr[ 'orderId'];
				$trade_no=@$result_arr[ 'queryId'];
				return new \LSYS\PayGateway\Pay\PayResult\FailResult($html,$result_arr["origRespMsg"],$out_trade_no, $trade_no);
			}
		} else if ($result_arr["respCode"] == "03"
				|| $result_arr["respCode"] == "04"
				|| $result_arr["respCode"] == "05" ){
				    return (new \LSYS\PayGateway\Pay\PayResult\FailResult($html,'Try later'))->setLocalFail();
		} else {
		    return (new \LSYS\PayGateway\Pay\PayResult\FailResult($html,$result_arr["respMsg"]))->setLocalFail();
		}
	}
	/**
	 * check upacp retrun to app data
	 * @param string $data
	 * @return bool
	 */
	public function validateAppResponse($data){
		$verify_cert_dir=$this->_config->getVerifyCertDir();
		
		if ($this->_config->getMode()=='sandbox'){
			require_once (__DIR__.'/../../../../../libs/upacp_apple_pay/sdk/SDKConfigDev.php');
		}else{
			require_once (__DIR__.'/../../../../../libs/upacp_apple_pay/sdk/SDKConfig.php');
		}
		
		require_once (__DIR__.'/../../../../../libs/upacp_apple_pay/sdk/acp_service.php');
		return \com\unionpay\acp\sdk\AcpService::validateAppResponse($data,$verify_cert_dir);
	}
}