<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Upacp;
use LSYS\PayGateway\Pay\PayAdapterCallback;
use LSYS\PayGateway\Pay\PayRender;
use LSYS\PayGateway\Pay\QueryParam;
use LSYS\PayGateway\Pay\PayParam;
use LSYS\PayGateway\Pay\Query;
use LSYS\PayGateway\Pay\PayAdapterNotify;
class Pay implements \LSYS\PayGateway\Pay\PayAdapterSimple, PayAdapterCallback,PayAdapterNotify,Query{
	/**
	 * @var PayConfig
	 */
	protected $_config;
	public function __construct(PayConfig $config){
		$this->_config=$config;
	}
	public static function supportType($type){
	    return $type&(self::TYPE_PC|self::TYPE_WAP|self::TYPE_WECHAT);
	}
	/**
	 * {@inheritDoc}

	 */
	public function payRender(PayParam $pay_param){
		$notify_url=$this->_config->getNotifyUrl();
		$return_url=$this->_config->getReturnUrl();
		
		
		$merid=$this->_config->getMerid();
		$sdk_sign_cert_path=$this->_config->getSignCertPath();
		$sdk_sign_cert_pwd=$this->_config->getSignCertPwd();
		
		
		$show_url=$pay_param->getShowUrl();
		$out_trade_no=$pay_param->getSn();
		$total_fee=intval($pay_param->getPayMoney()*100);
		$subject=$pay_param->getTitle();
		$body=$pay_param->getBody();
		
		$ctime=$pay_param->getCreateTime();
		
		
		if ($this->_config->getMode()=='sandbox'){
			require_once (__DIR__.'/../../../../../libs/upacp_sdk_php/utf8/func/SDKConfigDev.php');
		}else{
			require_once (__DIR__.'/../../../../../libs/upacp_sdk_php/utf8/func/SDKConfig.php');
		}
		
		require_once (__DIR__.'/../../../../../libs/upacp_sdk_php/utf8/func/common.php');
		require_once (__DIR__.'/../../../../../libs/upacp_sdk_php/utf8/func/secureUtil.php');
		require_once (__DIR__.'/../../../../../libs/upacp_sdk_php/utf8/func/log.class.php');
		
		
		
		/**
		 *	以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己需要，按照技术文档编写。该代码仅供参考
		 */
		// 初始化日志
		$params = array(
			'version' => '5.0.0',				//版本号
			'encoding' => 'utf-8',				//编码方式
			'certId' => getCertId ( $sdk_sign_cert_path,$sdk_sign_cert_pwd ),			//证书ID
			'txnType' => '01',				//交易类型
			'txnSubType' => '01',				//交易子类
			'bizType' => '000201',				//业务类型
			'frontUrl' =>  $return_url,  		//前台通知地址
			'backUrl' => $notify_url,		//后台通知地址
			'signMethod' => '01',		//签名方法
			'channelType' => '08',		//渠道类型，07-PC，08-手机
			'accessType' => '0',		//接入类型
			'merId' => $merid,		        //商户代码，请改自己的测试商户号
			'orderId' => $out_trade_no,	//商户订单号
			'txnTime' => date('YmdHis',$ctime),	//订单发送时间
			'txnAmt' => strval($total_fee),		//交易金额，单位分
			'currencyCode' => '156',	//交易币种
			'defaultPayType' => '0001',	//默认支付方式
			'orderDesc' => $subject,  //订单描述，网关支付和wap支付暂时不起作用
			'reqReserved' =>$body, //请求方保留域，透传字段，查询、通知、对账文件中均会原样出现
		);
		
		// 签名
		sign ( $params,$sdk_sign_cert_path );
		
		// 前台请求地址
		$front_uri = SDK_FRONT_TRANS_URL;
		// 构造 自动提交的表单
		$html_form = create_html ( $params, $front_uri );
		return new PayRender(PayRender::OUT_HTML, $html_form);
	}
	protected function _verify(){
		if ($this->_config->getMode()=='sandbox'){
			require_once (__DIR__.'/../../../../../libs/upacp_sdk_php/utf8/func/SDKConfigDev.php');
		}else{
			require_once (__DIR__.'/../../../../../libs/upacp_sdk_php/utf8/func/SDKConfig.php');
		}
		require_once (__DIR__.'/../../../../../libs/upacp_sdk_php/utf8/func/common.php');
		require_once (__DIR__.'/../../../../../libs/upacp_sdk_php/utf8/func/secureUtil.php');
		if(!isset($_POST ['signature']))return false;
		
		$verify_cert_dir=$this->_config->getVerifyCertDir();
		
		return verify ( $_POST,$verify_cert_dir );
	}
	public function payCallback(){
		if(!$this->_verify()){
		    return (new \LSYS\PayGateway\Pay\PayResult\FailResult($_POST,'sign is fail'))
		    ->setParam($_POST)
		      ->setSignFail();
		}
		//Loger::instance(Loger::TYPE_PAY_CALLBACK)->add($this->supportName(),$_POST);
		if(@$_POST['respCode']!='00'){
		    return (new \LSYS\PayGateway\Pay\PayResult\FailResult($_POST,@$_POST['respMsg']))
		    ->setParam($_POST)
		    ->setLocalFail();
		}
		$out_trade_no=$_POST[ 'orderId'];
		$trade_no=$_POST[ 'queryId'];
		$accNo=$_POST[ 'accNo'];
		$money=$_POST[ 'txnAmt'];
		$money=$money/100;
		$result=new \LSYS\PayGateway\Pay\PayResult\SuccResult($_POST,$out_trade_no,$trade_no);
		$result->setMoney($money)->setPayAccount($accNo)->setParam($_POST);
		return $result;
	}
	public function payNotify(){
		ignore_user_abort(true);
		if(!$this->_verify()){
		    return  (new \LSYS\PayGateway\Pay\PayResult\FailResult($_POST,'sign is fail'))
		    ->setParam($_POST)
		    ->setSignFail();
		}
		////Loger::instance(Loger::TYPE_PAY_NOTIFY)->add($this->supportName(),$_POST);
		if(@$_POST['respCode']!='00'){
		    return  (new \LSYS\PayGateway\Pay\PayResult\FailResult($_POST,@$_POST['respMsg']))
		    ->setParam($_POST)
		    ->setLocalFail();
		}
		$out_trade_no=$_POST[ 'orderId'];
		$trade_no=$_POST[ 'queryId'];
		$accNo=$_POST[ 'accNo'];
		$money=$_POST[ 'txnAmt'];
		$money=$money/100;
		$result=new \LSYS\PayGateway\Pay\PayResult\SuccResult($_POST,$out_trade_no,$trade_no);
		$result->setMoney($money)->setPayAccount($accNo)->setParam($_POST);
		return   $result;
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
		if ($this->_config->getMode()=='sandbox'){
			require_once (__DIR__.'/../../../../../libs/upacp_sdk_php/utf8/func/SDKConfigDev.php');
		}else{
			require_once (__DIR__.'/../../../../../libs/upacp_sdk_php/utf8/func/SDKConfig.php');
		}
		
		require_once (__DIR__.'/../../../../../libs/upacp_sdk_php/utf8/func/common.php');
		require_once (__DIR__.'/../../../../../libs/upacp_sdk_php/utf8/func/secureUtil.php');
		require_once (__DIR__.'/../../../../../libs/upacp_sdk_php/utf8/func/httpClient.php');
		require_once (__DIR__.'/../../../../../libs/upacp_sdk_php/utf8/func/log.class.php');
		
		
		$merid=$this->_config->getMerid();
		$sdk_sign_cert_path=$this->_config->getSignCertPath();
		$sdk_sign_cert_pwd=$this->_config->getSignCertPwd();
		
		$out_trade_no=$param->getPaySn();
		$ctime=$param->getCreateTime();
		
		$params = array(
				'version' => '5.0.0',		//版本号
				'encoding' => 'utf-8',		//编码方式
				'certId' => getCertId ( $sdk_sign_cert_path,$sdk_sign_cert_pwd ),			//证书ID
				'signMethod' => '01',		//签名方法
				'txnType' => '00',		//交易类型
				'txnSubType' => '00',		//交易子类
				'bizType' => '000000',		//业务类型
				'accessType' => '0',		//接入类型
				'channelType' => '07',		//渠道类型
				'orderId' => $out_trade_no,	//商户订单号
				'merId' => $merid,		        //商户代码，请改自己的测试商户号
				'txnTime' => date('YmdHis',$ctime),	//订单发送时间
		);

		// 签名
		sign ( $params,$sdk_sign_cert_path );
		
		$result = sendHttpRequest ( $params, SDK_SINGLE_QUERY_URL );
		//返回结果展示
		$result_arr = coverStringToArray ( $result );
	
		$verify_cert_dir=$this->_config->getVerifyCertDir();
		
		if (!verify ( $result_arr ,$verify_cert_dir)){
		    return (new \LSYS\PayGateway\Pay\PayResult\FailResult($result,'sign is fail'))->setSignFail();
		}
		if ($result_arr["respCode"] == "00"){
			if ($result_arr["origRespCode"] == "00"){
				$out_trade_no=$result_arr[ 'orderId'];
				$trade_no=$result_arr[ 'queryId'];
				return (new \LSYS\PayGateway\Pay\PayResult\SuccResult($result,$out_trade_no, $trade_no))
					->setParam($result_arr)
					->setMoney($result_arr['txnAmt']/100)
					->setPayAccount($result_arr['accNo']);
			} else if ($result_arr["origRespCode"] == "03"
					|| $result_arr["origRespCode"] == "04"
					|| $result_arr["origRespCode"] == "05"){
						$out_trade_no=$result_arr[ 'orderId'];
						$trade_no=@$result_arr[ 'queryId'];
						return (new \LSYS\PayGateway\Pay\PayResult\IngResult($result,$out_trade_no,$trade_no))->setParam($result_arr);
			} else {
				$out_trade_no=$result_arr[ 'orderId'];
				$trade_no=@$result_arr[ 'queryId'];
				return (new \LSYS\PayGateway\Pay\PayResult\FailResult($result,$result_arr["origRespMsg"],$out_trade_no, $trade_no))->setParam($result_arr);
			}
		} else if ($result_arr["respCode"] == "03"
				|| $result_arr["respCode"] == "04"
				|| $result_arr["respCode"] == "05" ){
				    return (new \LSYS\PayGateway\Pay\PayResult\FailResult($result,'Try later'))->setLocalFail()->setParam($result_arr);
		} else {
		    return (new \LSYS\PayGateway\Pay\PayResult\FailResult($result,$result_arr["respMsg"]))->setLocalFail()->setParam($result_arr);
		}
	}
	
}