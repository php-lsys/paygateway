<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Transfers;
use LSYS\PayGateway\Param;
use LSYS\PayGateway\Pay\Money;
use LSYS\PayGateway\Utils;

class TransfersParam implements Param{
	protected $_param=array();
	public function __construct($pay_account,$pay_name,$money,$transfers_no=null){
		if ($transfers_no==null)$transfers_no=Utils::snnoCreate('LT');
		$this->_param=array(
			'pay_account'=>$pay_account,
			'pay_name'=>$pay_name,
			'pay_money'=>Money::factroy($money),
			'transfers_no'=>$transfers_no,
			'msg'=>'transfers',
			'extra'=>array(),
		);
	}
	public function setExtra($param){
		$this->_param['extra']=$param;
		return $this;
	}
	public function setPayMsg($msg){
		$this->_param['msg']=$msg;
		return $this;
	}
	public function getPayAccount(){
		return $this->_param['pay_account'];
	}
	public function getPayName(){
		return $this->_param['pay_name'];
	}
	public function getMoney(){
		return $this->_param['pay_money'];
	}
	public function getPayMoney($currency=Money::CNY){
		$money=$this->_param['pay_money']->to($currency);
		if ($money<=0) return 0;
		return $money;
	}
	public function getTransfersNo(){
		return $this->_param['transfers_no'];
	}
	public function getPayMsg(){
		return $this->_param['msg'];
	}
	public function getExtra(){
		return $this->_param['extra'];
	}
	public function asArray(){
		return $this->_param;
	}
	
}