<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Pay;
use LSYS\PayGateway\Param;
use LSYS\PayGateway\Utils;

class RefundParam implements Param{
	/**
	 * @param float $money
	 * @return \LSYS\PayGateway\Pay\PayParam
	 */
	public static function factory($pay_sn,$pay_no,$total_money,$refund_money){
		return new PayParam($pay_sn,$pay_no,$total_money,$refund_money);
	}
	protected $_param=array();
	public function __construct($pay_sn,$pay_no,$total_money,$refund_money){
		$this->_param['pay_no']=$pay_no;
		$this->_param['pay_sn']=$pay_sn;
		$this->_param['total_money']=Money::factroy($total_money);
		$this->_param['money']=Money::factroy($refund_money);
	}
	public function setReturnNo($return_no){
		$this->_param['return_no']=$return_no;
		return $this;
	}
	public function setRefundMsg($msg){
		$this->_param['msg']=$msg;
		return $this;
	}
	public function getReturnNo(){
		if (empty($this->_param['return_no'])){
			$this->_param['return_no']=Utils::snnoCreate('LR');
		}
		return $this->_param['return_no'];
	}
	public function getRefundMsg(){
		if(empty($this->_param['msg'])) return '';
		return $this->_param['msg'];
	}
	public function getPaySn(){
		return $this->_param['pay_sn'];
	}
	public function getPayNo(){
		return $this->_param['pay_no'];
	}
	/**
	 * @return Money
	 */
	public function getRefundMoney(){
		return $this->_param['money'];
	}
	/**
	 * @return Money
	 */
	public function getTotalMoney(){
		return $this->_param['total_money'];
	}
	public function getRefundPayMoney($currency=Money::CNY){
		$money=$this->_param['money']->to($currency);
		if ($money<=0) return 0;
		$total=$this->getTotalPayMoney($currency);
		$money=$money<=$total?$money:$total;
		return Utils::moneyFormat($money);
	}
	public function getTotalPayMoney($currency=Money::CNY){
		$money=$this->_param['total_money']->to($currency);
		if ($money<=0) return 0;
		return $money;
	}
	public function asArray(){
		return $this->_param;
	}
}