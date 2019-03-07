<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Pay;
use LSYS\PayGateway\Param;
class QueryParam implements Param{
	protected $_param=array();
	public function __construct($pay_sn,$pay_no,$create_time){
		$this->_param['pay_sn']=$pay_sn;
		$this->_param['pay_no']=$pay_no;
		$this->_param['ctime']=$create_time;
	}
	public function getCreateTime(){
		return $this->_param['ctime'];
	}
	public function getPayNo(){
		return $this->_param['pay_no'];
	}
	public function getPaySn(){
		return $this->_param['pay_sn'];
	}
	public function asArray(){
		return $this->_param;
	}
}