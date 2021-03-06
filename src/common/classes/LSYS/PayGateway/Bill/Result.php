<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Bill;
class Result{
	//该账单记录时付款记录
	const TYPE_PAY=1;
	//该记录是退款记录
	const TYPE_REFUND=1<<1;
	//该记录是提现记录
	const TYPE_TRANSFERS=1<<2;
	//该记录是其他未知记录
	const TYPE_OTHER=0;
	protected $_type;
	protected $_pay_sn;
	protected $_pay_no;
	protected $_pay_account;
	protected $_money;
	protected $_fee_money;
	protected $_pay_time;
	protected $_param;
	public function __construct($type,$pay_sn,$pay_no,$pay_account,$money,$fee_money,$pay_time,$param=array()){
		//订单号 支付号 支付金额 手续费 交易时间 其他
		$this->_type=$type;
		$this->_pay_sn=$pay_sn;
		$this->_pay_no=$pay_no;
		$this->_pay_account=$pay_account;
		$this->_money=$money;
		$this->_fee_money=$fee_money;
		$this->_pay_time=$pay_time;
		$this->_param=$param;
	}
	public function isType($type){
		return $this->_type&$type=$type;
	}
	public function getType(){
		return $this->_type;
	}
	public function getPaySn(){
		return $this->_pay_sn;
	}
	public function getPayNo(){
		return $this->_pay_no;
	}
	public function getPayAccount(){
		return $this->_pay_account;
	}
	public function getMoney(){
		return $this->_money;
	}
	public function getFeeMoney(){
		return $this->_fee_money;
	}
	public function getPayTime(){
		return $this->_pay_time;
	}
	public function getParam($key=NULL){
		if ($key===null) return $this->_param;
		if (!isset($this->_param[$key])) return null;
		return $this->_param[$key];
	}
}