<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Baidu;
use LSYS\PayGateway\Pay\PayAdapterCallback;
use LSYS\PayGateway\Exception;
use LSYS\PayGateway\Pay\PayParam;
use LSYS\PayGateway\Pay\PayRender;
use LSYS\PayGateway\Pay\Query;
use LSYS\PayGateway\Pay\QueryParam;

use LSYS\PayGateway\Pay\PayAdapterNotify;
class PayWap implements \LSYS\PayGateway\Pay\PayAdapterSimple, PayAdapterNotify,PayAdapterCallback,Query{
	/**
	 * @var PayConfig
	 */
	protected $_config;
	/**
	 * @var \bdpay_sdk
	 */
	protected $_pay_sdk;
	public function __construct(PayConfig $config){
		$this->_config=$config;
	}
	public static function supportType($type){
	    return $type&(self::TYPE_WAP|self::TYPE_WECHAT);
	}
	/**
	 * {@inheritDoc}

	 */
	public function payRender(PayParam $pay_param){
		
		
		require_once (__DIR__."/../../../../../libs/bdpayh5php/bdpay_sdk.php");
		require_once (__DIR__."/../../../../../libs/bdpayh5php/bdpay_pay.cfg.php");
		
		\sp_conf::$config=array(
				'sp_no'=>$this->_config->getSpNo(),
				'key_file'=>$this->_config->getKeyFile()
		);
		
		$timeout=$pay_param->getTimeout();
		$timeout||$timeout=time()+3600*24*7;
		
		
		$notify_url=$this->_config->getNotifyUrl();
		$_return_url=$this->_config->getReturnUrl();
		
		
		$show_url=$pay_param->getShowUrl();
		$out_trade_no=$pay_param->getSn();
		$total_fee=intval($pay_param->getPayMoney()*100);
		$subject=$pay_param->getTitle();
		$body=$pay_param->getBody();

		$ctime=$pay_param->getCreateTime();
		
		
		$bdpay_sdk = new \bdpay_sdk();
		$order_create_time = date("YmdHis",$ctime);
		$expire_time = date('YmdHis', $timeout);
		$order_no = $out_trade_no;
		$good_name = $subject;
		$good_desc = $body;
		$goods_url = $show_url;
		$unit_amount = $total_fee;
		$unit_count = 1;
		$transport_amount = 0;
		$total_amount = $total_fee;
		$buyer_sp_username = '';
		$return_url = $notify_url;
		$page_url = $_return_url;
		$pay_type = 1;
		$bank_no ='';
		$extra = '';
		
		/*
		 * 字符编码转换，百度钱包默认的编码是GBK，商户网页的编码如果不是，请转码。涉及到中文的字段请参见接口文档
		 * 步骤：
		 * 1. URL转码
		 * 2. 字符编码转码，转成GBK
		 *
		 * $good_name = iconv("UTF-8", "GBK", urldecode($good_name));
		 * $good_desc = iconv("UTF-8", "GBK", urldecode($good_desc));
		 *
		 */
		$good_name = iconv("UTF-8", "GBK", urldecode($good_name));
		$good_desc = iconv("UTF-8", "GBK", urldecode($good_desc));
		// 用于测试的商户请求支付接口的表单参数，具体的表单参数各项的定义和取值参见接口文档
		$params = array (
				'service_code' => \sp_conf::BFB_PAY_INTERFACE_SERVICE_ID,
		    'sp_no' => \sp_conf::SP_NO(),
				'order_create_time' => $order_create_time,
				'order_no' => $order_no,
				'goods_name' => $good_name,
				'goods_desc' => $good_desc,
				'goods_url' => $goods_url,
				'unit_amount' => $unit_amount,
				'unit_count' => $unit_count,
				'transport_amount' => $transport_amount,
				'total_amount' => $total_amount,
				'currency' => \sp_conf::BFB_INTERFACE_CURRENTCY,
				'buyer_sp_username' => $buyer_sp_username,
				'return_url' => $return_url,
				'page_url' => $page_url,
				'pay_type' => $pay_type,
				'bank_no' => $bank_no,
				'expire_time' => $expire_time,
				'input_charset' => \sp_conf::BFB_INTERFACE_ENCODING,
				'version' => \sp_conf::BFB_INTERFACE_VERSION,
				'sign_method' => \sp_conf::SIGN_METHOD_MD5,
				'extra' =>$extra
		);
		
		$order_url = $bdpay_sdk->create_baifubao_pay_order_url($params, \sp_conf::BFB_PAY_WAP_DIRECT_NEEDLOGIN_URL);
		if(false === $order_url){
			throw new Exception('create the url for baifubao pay interface failed');
		}
		return new PayRender(PayRender::OUT_URL, $order_url);
	}
	
	
	public function payCallback(){
		require_once (__DIR__."/../../../../../libs/bdpayh5php/bdpay_sdk.php");
		require_once (__DIR__."/../../../../../libs/bdpayh5php/bdpay_pay.cfg.php");
		
		\sp_conf::$config=array(
				'sp_no'=>$this->_config->getSpNo(),
				'key_file'=>$this->_config->getKeyFile()
		);
		
		
		$bdpay_sdk = new \bdpay_sdk();
		
		if (false === $bdpay_sdk->check_bfb_pay_result_notify()) {
		    return (new \LSYS\PayGateway\Pay\PayResult\FailResult($_GET,'sign is fail'))->setSignFail();
		}
		//Loger::instance(Loger::TYPE_PAY_NOTIFY)->add($this->supportName(),$_GET);
		// 查询订单在商户自己系统的状态
		$out_trade_no=$_GET ['order_no'];
		$trade_no=$_GET['bfb_order_no'];
		$buyer_email='';
		$total_fee=$_GET['total_amount'];
		$result=(new \LSYS\PayGateway\Pay\PayResult\SuccResult($_GET,$out_trade_no,$trade_no));
		$result->setMoney($total_fee)->setPayAccount($buyer_email)->setParam($_GET);
		return $result;
		
	}
	public function payNotify(){
		ignore_user_abort(true);
		require_once (__DIR__."/../../../../../libs/bdpayh5php/bdpay_sdk.php");
		require_once (__DIR__."/../../../../../libs/bdpayh5php/bdpay_pay.cfg.php");
		
		\sp_conf::$config=array(
				'sp_no'=>$this->_config->getSpNo(),
				'key_file'=>$this->_config->getKeyFile()
		);
		

		$this->_pay_sdk=$bdpay_sdk = new \bdpay_sdk();
		
		if (false === $bdpay_sdk->check_bfb_pay_result_notify()) {
		    return (new \LSYS\PayGateway\Pay\PayResult\FailResult($_GET,iconv("gb2312","utf-8", $bdpay_sdk->err_msg)))->setLocalFail();
		}
		
		// 查询订单在商户自己系统的状态
		$out_trade_no=$_GET ['order_no'];
		$trade_no=$_GET['bfb_order_no'];
		$buyer_email='';
		$total_fee=$_GET['total_amount'];
		$result=new \LSYS\PayGateway\Pay\PayResult\SuccResult($_GET,$out_trade_no,$trade_no);
		$result->setMoney($total_fee)->setPayAccount($buyer_email)->setParam($_GET);
		return  $result;
		
		/*
		 * 此处是商户收到百度钱包支付结果通知后需要做的自己的具体业务逻辑，比如记账之类的。 只有当商户收到百度钱包支付 结果通知后，
		 * 所有的预处理工作都返回正常后，才执行该部分
		 */
		
		// 向百度钱包发起回执
		$bdpay_sdk->notify_bfb();
		
	}
	public function payNotifyOutput($status=true,$msg=null){
		if($this->_pay_sdk)die('fail');
		if ($status){
			$this->_pay_sdk->notifyBfb();
			die();
		}else{
			http_response_code(500);
			die($msg);
		}
	}
	
	
	
	public function query(QueryParam $param){
		require_once (__DIR__."/../../../../../libs/bdpayh5php/bdpay_sdk.php");
		require_once (__DIR__."/../../../../../libs/bdpayh5php/bdpay_pay.cfg.php");
		
		\sp_conf::$config=array(
				'sp_no'=>$this->_config->getSpNo(),
				'key_file'=>$this->_config->getKeyFile()
		);
		$bdpay_sdk = new \bdpay_sdk();	
		$order_no = $param->getPaySn();
		// 用于测试的商户请求支付接口的表单参数，具体的表单参数各项的定义和取值参见接口文档
		
		$content = $bdpay_sdk->query_baifubao_pay_result_by_order_no($order_no,$res);
		
		if(false === $content){
		    return (new \LSYS\PayGateway\Pay\PayResult\FailResult($res,iconv("gb2312", 'utf-8', $bdpay_sdk->err_msg)))->setLocalFail();
		}
		else {
		    return (new \LSYS\PayGateway\Pay\PayResult\SuccResult($res,$content ['order_no'],$content ['bfb_order_no']))
		    ->setParam($content);
		}
	}
}