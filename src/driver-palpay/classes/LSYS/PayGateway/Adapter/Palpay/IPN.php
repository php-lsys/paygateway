<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Palpay;

use PayPal\IPN\PPIPNMessage;
use LSYS\PayGateway\Pay\PayResult;
use LSYS\PayGateway\Pay\Money;
use LSYS\PayGateway\Pay\PayAdapterNotify;
use LSYS\PayGateway\Pay\RefundNotify;
class IPN implements PayAdapterNotify,RefundNotify{
	const TYPE_INVALID=-1;
	const TYPE_UNKOWN=0;
	const TYPE_PAYCALLBACK=1;
	const TYPE_REFUND=2;
	/**
	 * @var Config
	 */
	protected $_config;
	protected $_pay_result;
	protected $_refund_result;
	
	
	public function __construct(Config $config){
		$this->_config=$config;
		ignore_user_abort(true);
	}
	protected function _config(){
		$mode=$this->_config->getMode();//"sandbox",'live'
		$username=$this->_config->getUsername();//"362724880-facilitator_api1.qq.com"
		$password=$this->_config->getPassword();//"RDYZD6FAEK28865K"
		$sign=$this->_config->getSignature();//"AFcWxV21C7fd0v3bYYYRCpSSRl31AFOI1naadHvh1c1vzVqRCY9c2mFZ"
	
		$config = array(
				"mode" => $mode,
				'log.LogEnabled' => false,
				'log.FileName' => sys_get_temp_dir().'/PayPal.log',
				'log.LogLevel' => 'FINE',
				"acct1.UserName" => $username,
				"acct1.Password" => $password,
				"acct1.Signature" => $sign,
		);
		return $config;
	}
	public function getType(){
		$config=$this->_config();
		$ipnMessage = new PPIPNMessage(null,$config);
		$raw=file_get_contents('php://input');
		if(!$ipnMessage->validate()) {
		    $result=new \LSYS\PayGateway\Pay\PayResult\FailResult($raw,'sign fail');
		    $this->_pay_result=$result->setSignFail();
			return IPN::TYPE_UNKOWN;
		}
		$data=$ipnMessage->getRawData();
		switch ($data['payment_status']){
			case 'Completed':
				
				// mc_gross=20.00
				// invoice=ni_ma_le_ge_b5844fd77e096a
				// protection_eligibility=Eligible
				// address_status=confirmed
				// payer_id=6ZZGEUUAELVPJ
				// tax=0.00
				// address_street=1%2CMain+St
				// payment_date=21%3A40%3A59+Dec+04%2C+2016+PST
				// payment_status=Completed
				// charset=gb2312
				// address_zip=78701
				// first_name=test
				// mc_fee=0.98
				// address_country_code=US
				// address_name=John
				// notify_version=3.8
				// custom=
				// payer_status=verified
				// address_country=United+States
				// address_city=Austin
				// quantity=1
				// verify_sign=AI36sk2Aln3iC.t.mla1wMizPRcQASQlHzkHUW5xmU8Bglpga9BontvB
				// payer_email=362724880-buyer%40qq.com
				// txn_id=8NP85109KC933221E
				// payment_type=instant
				// last_name=buyer
				// address_state=TX
				// receiver_email=362724880-facilitator%40qq.com
				// payment_fee=0.98
				// receiver_id=JNX3TFBF7HZW6
				// txn_type=express_checkout
				// item_name=
				// mc_currency=USD
				// item_number=
				// residence_country=CN
				// test_ipn=1
				// handling_amount=0.00
				// transaction_subject=
				// payment_gross=20.00
				// shipping=0.00
				// ipn_track_id=76b574071f406
				
				
				//Loger::instance(Loger::TYPE_PAY_NOTIFY)->add(Pay::NAME,$data);
				// 查询订单在商户自己系统的状态
				$out_trade_no=$data['invoice'];
				$trade_no=$data['txn_id'];
				$buyer_email=$data['payer_email'];
				$total_fee=$data['payment_gross'];
				$mc_currency=$data['mc_currency'];
				$result=new \LSYS\PayGateway\Pay\PayResult\SuccResult($raw,$out_trade_no,$trade_no);
				$result->setMoney(Money::factroy($total_fee,$this->_config->getCurrency($mc_currency)))
				->setPayAccount($buyer_email)->setParam($data);
				$this->_pay_result=$result;
				return IPN::TYPE_PAYCALLBACK;
			break;
			case 'Refunded':
				
				// 		mc_gross=-1.00
				// 		invoice=ni_ma_le_ge_b5845020f5d290
				// 		protection_eligibility=Eligible
				// 		payer_id=6ZZGEUUAELVPJ
				// 		address_street=1%2CMain+St
				// 		payment_date=22%3A00%3A30+Dec+04%2C+2016+PST
				// 		payment_status=Refunded
				// 		charset=gb2312
				// 		address_zip=78701
				// 		first_name=test
				// 		mc_fee=-0.04
				// 		address_country_code=US
				// 		address_name=John
				// 		notify_version=3.8
				// 		reason_code=refund
				// 		custom=
				// 		address_country=United+States
				// 		address_city=Austin
				// 		verify_sign=AdkANPdf8HIO.xJYcRY58Cz1f8dqAryBCiMPZ7Zf8N3sVJsZLYi2VkAh
				// 		payer_email=362724880-buyer%40qq.com
				// 		parent_txn_id=34C10742S03834219
				// 		txn_id=9BN16185V2183693X
				// 		payment_type=instant
				// 		last_name=buyer
				// 		address_state=TX
				// 		receiver_email=362724880-facilitator%40qq.com
				// 		payment_fee=-0.04
				// 		receiver_id=JNX3TFBF7HZW6
				// 		item_name=
				// 		mc_currency=USD
				// 		item_number=
				// 		residence_country=CN
				// 		test_ipn=1
				// 		handling_amount=0.00
				// 		transaction_subject=
				// 		payment_gross=-1.00
				// 		shipping=0.00
				// 		ipn_track_id=79f6f5f71175
				
				
				//Loger::instance(Loger::TYPE_REFUND)->add(Pay::NAME,$data);
				$batch_no=$data['invoice'];
				$dbref=$data['ipn_track_id'];
				$result=new \LSYS\PayGateway\Pay\RefundResult\SuccResult($raw,$batch_no,$dbref);
				$this->_refund_result=$result->setParam($data);
				return IPN::TYPE_REFUND;
			break;
		}
		return IPN::TYPE_UNKOWN;
	}
	/**
	 * pay notify
	 * @return PayResult
	 */
	public function payNotify(){
	    if ($this->_pay_result==null) return (new \LSYS\PayGateway\Pay\PayResult\FailResult(null,'unkown'))->setLocalFail();
		return $this->_pay_result;
	}
	/**
	 * pay notify
	 */
	public function payNotifyOutput($status,$msg=''){
		$this->output($status,$msg);
	}
	
	/**
	 * pay notify
	 * @return PayResult
	 */
	public function refundNotify(){
	    if ($this->_refund_result==null) return (new \LSYS\PayGateway\Pay\RefundResult\FailResult(null,'refund result is null'))->setLocalFail();
		return $this->_refund_result;
	}
	/**
	 * pay notify
	 */
	public function refundNotifyOutput($status=true,$msg=null){
		$this->output($status,$msg);
	}
	public function output($status,$msg=''){
		if ($status){
			http_response_code(200);
			die('ok');
		}else{
			http_response_code(500);
			die($msg);
		}
	}
}