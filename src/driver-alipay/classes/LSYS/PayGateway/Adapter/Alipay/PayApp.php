<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Alipay;
use LSYS\PayGateway\Exception;
use LSYS\PayGateway\Pay\PayParam;
class PayApp extends Alipay{
	public function __construct(PayConfig $config){
		parent::__construct($config);
		$this->_config->setRsa();
	}
	public static function supportType($type){
	    return ($type&(self::TYPE_ANDROID|self::TYPE_IOS))&&(!($type&self::TYPE_WECHAT));
	}
	/**
	 * {@inheritDoc}

	 */
	public function payRender(PayParam $pay_param){
		throw new Exception('not support the method');
	}
	public function payNotify(){
		ignore_user_abort(true);
		$alipay_config=$this->_config->asArray();
		require_once (__DIR__."/../../../../../libs/alipay_app/lib/alipay_notify.class.php");
		$alipayNotify = new \AlipayNotify($alipay_config);
		if(!isset($_POST["sign"])||!$alipayNotify->verifyNotify()){
		    return  (new \LSYS\PayGateway\Pay\PayResult\FailResult($_POST,'sign is fail'))->setSignFail()->setParam($_POST);
		}
		//Loger::instance(Loger::TYPE_PAY_NOTIFY)->add($this->supportName(),$_POST);
		$out_trade_no = $_POST['out_trade_no'];
		$trade_no = $_POST['trade_no'];
		$trade_status = $_POST['trade_status'];
		$total_fee=$_POST[ 'total_fee'];
		$buyer_email=$_POST[ 'buyer_email'];
		if($trade_status == 'TRADE_FINISHED') {
		    $result=(new \LSYS\PayGateway\Pay\PayResult\SuccResult($_POST,$out_trade_no,$trade_no))->setParam($_POST);
		}else if ($trade_status == 'TRADE_SUCCESS') {
		    $result=(new \LSYS\PayGateway\Pay\PayResult\SuccResult($_POST,$out_trade_no,$trade_no))->setParam($_POST);
		}else{
		    $result=(new \LSYS\PayGateway\Pay\PayResult\FailResult($_POST,$trade_status,$out_trade_no,$trade_no))->setParam($_POST);
		}
		$result->setMoney($total_fee)->setPayAccount($buyer_email);
		return $result;
	}
}