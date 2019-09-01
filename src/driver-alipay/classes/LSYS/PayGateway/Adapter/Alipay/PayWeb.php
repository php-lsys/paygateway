<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Alipay;
use LSYS\PayGateway\Pay\PayAdapterCallback;

use LSYS\PayGateway\Utils;
use LSYS\PayGateway\Pay\PayParam;
use LSYS\PayGateway\Pay\PayRender;

class PayWeb extends Alipay implements PayAdapterCallback{
	public function __construct(PayConfig $config){
		parent::__construct($config);
		$this->_config->setMd5();
	}
	public static function supportType($type){
	    return ($type&self::TYPE_PC)&&(!($type&self::TYPE_WECHAT));
	}
	/**
	 * {@inheritDoc}

	 */
	public function payRender(PayParam $pay_param){
		$alipay_config=$this->_config->asArray();
		$seller_email =$this->_config->getSellerId();
		
		$payment_type = "1";
		$notify_url=$this->_config->getNotifyUrl();
		$return_url=$this->_config->getReturnUrl();
		$show_url=$pay_param->getShowUrl();
		
		$out_trade_no=$pay_param->getSn();
		$total_fee=$pay_param->getPayMoney();
		$subject=$pay_param->getTitle();
		$body=$pay_param->getBody();
		
		
		$anti_phishing_key = "";
		$exter_invoke_ip = Utils::clientIp();
		$parameter = array(
				"service" => "create_direct_pay_by_user",
				"partner" => trim($alipay_config['partner']),
				"payment_type"	=> $payment_type,
				"notify_url"	=> $notify_url,
				"return_url"	=> $return_url,
				"seller_email"	=> $seller_email,
				"out_trade_no"	=> $out_trade_no,
				"subject"	=> $subject,
				"total_fee"	=> $total_fee,
				"body"	=> $body,
				"show_url"	=> $show_url,
				"anti_phishing_key"	=> $anti_phishing_key,
				"exter_invoke_ip"	=> $exter_invoke_ip,
				"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
		);
		require_once (__DIR__."/../../../../../libs/alipay_direct/lib/alipay_submit.class.php");
		//建立请求
		$alipaySubmit = new \AlipaySubmit($alipay_config);
		$html_text=$alipaySubmit->buildRequestForm($parameter,"get", "");
		return new PayRender(PayRender::OUT_HTML, $html_text);
	}
	protected function _verify(){
		$alipay_config=$this->_config->asArray();
		require_once (__DIR__."/../../../../../libs/alipay_direct/lib/alipay_notify.class.php");
		return new \AlipayNotify($alipay_config);
	}
	public function payCallback(){
		$alipayNotify=$this->_verify();
		if(!isset($_GET["sign"])||!$alipayNotify->verifyReturn()){
		    return (new \LSYS\PayGateway\Pay\PayResult\FailResult($_GET,'sign is fail'))->setSignFail();
		}
		$trade_status=$_GET['trade_status'];
		$out_trade_no=@$_GET['out_trade_no'];
		$trade_no=@$_GET['trade_no'];
		if($trade_status== 'TRADE_FINISHED' || $trade_status == 'TRADE_SUCCESS' ){
		    $result= (new \LSYS\PayGateway\Pay\PayResult\SuccResult($_GET,$out_trade_no,$trade_no))->setParam($_GET);
		}else $result= (new \LSYS\PayGateway\Pay\PayResult\FailResult($_GET,$trade_status,$out_trade_no,$trade_no))->setParam($_GET);
		$total_fee=$_GET['total_fee'];
		$buyer_email=$_GET['buyer_email'];
		$result->setMoney($total_fee)->setPayAccount($buyer_email);
		return $result;
	}
	public function payNotify(){
		ignore_user_abort(true);
		$alipayNotify=$this->_verify();
		if(!isset($_POST["sign"])||!$alipayNotify->verifyNotify()){
		    return (new \LSYS\PayGateway\Pay\PayResult\FailResult($_POST,'sign is fail'))->setSignFail();
		}
		$out_trade_no=$_POST['out_trade_no'];
		$trade_no=$_POST['trade_no'];
		$buyer_email=$_POST['buyer_email'];
		$total_fee=$_POST['total_fee'];
		//交易状态
		$trade_status = $_POST['trade_status'];
		if($_POST['trade_status'] == 'TRADE_FINISHED') {
		    $result=(new \LSYS\PayGateway\Pay\PayResult\SuccResult($_POST,$out_trade_no,$trade_no))->setParam($_POST);
		}else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
		    $result=(new \LSYS\PayGateway\Pay\PayResult\SuccResult($_POST,$out_trade_no,$trade_no))->setParam($_POST);
		}else{
		    $result=(new \LSYS\PayGateway\Pay\PayResult\FailResult($_POST,$_POST['trade_status'],$out_trade_no,$trade_no))->setParam($_POST);
		}
		$result->setMoney($total_fee)->setPayAccount($buyer_email);
		return  $result;
	}
}