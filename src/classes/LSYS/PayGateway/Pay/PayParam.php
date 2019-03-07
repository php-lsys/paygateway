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
class PayParam implements Param,\Serializable{
	/**
	 * @param float $money
	 * @return PayParam
	 */
	public static function factory($money,$sn=null){
		return new PayParam($money,$sn);
	}
	protected $_param=array();
	public function __construct($money,$sn=null){
		$this->_param['money']=Money::factroy($money);
		$this->_param['sn']=$sn;
		$this->_param['timeout']=0;
		$this->_param['ctimeout']=time();
		$this->_param['good_ids']=array();
	}
	public function getSn(){
		if (empty($this->_param['sn'])) $this->_param['sn']=Utils::snnoCreate('LO');
		return $this->_param['sn'];
	}
	/**
	 * @return Money
	 */
	public function getMoney(){
		return $this->_param['money'];
	}
	public function getPayMoney($currency=Money::CNY){
		$money=$this->_param['money']->to($currency);
		return $money<0.01?0.01:$money;
	}
	public function getTitle(){
		return empty($this->_param['title'])?("pay {$this->_param['money']}"):$this->_param['title'];
	}
	public function getBody(){
		return empty($this->_param['body'])?$this->getTitle():$this->_param['body'];
	}
	public function getShowUrl(){
		return empty($this->_param['show_url'])?$this->_defUrl():$this->_param['show_url'];
	}
	public function getCancelUrl(){
		return empty($this->_param['cancel_url'])?$this->_defUrl():$this->_param['cancel_url'];
	}
	public function getGoods(){
		return $this->_param['good_ids'];
	}
	public function getTimeout(){
		return $this->_param['timeout']<=0?0:$this->_param['timeout'];
	}
	protected function _defUrl(){
		if (isset($_SERVER['HTTP_HOST'])){
			if (isset($_SERVER['HTTPS'])&&strtoupper($_SERVER['HTTPS']) == 'ON'){
				$p='https://';
			}else $p='http://';
			if(isset($_SERVER['SERVER_PORT'])&&$_SERVER['SERVER_PORT']!='80')$pr=':'.$_SERVER['SERVER_PORT'];
			else $pr='';
			return $p.$_SERVER['HTTP_HOST'].$pr;
		}else return '/';
	}
	public function setCreateTime($timeout){
		$this->_param['ctimeout']=$timeout;
		return $this;
	}
	public function getCreateTime(){
		return $this->_param['ctimeout'];
	}
	public function setTimeout($timeout){
		$this->_param['timeout']=$timeout;
		return $this;
	}
	public function setTitle($title){
		$this->_param['title']=$title;
		return $this;
	}
	public function setBody($body){
		$this->_param['body']=$body;
		return $this;
	}
	public function setShowUrl($show_url){
		$this->_param['show_url']=$show_url;
		return $this;
	}
	public function setCancelUrl($cancel_url){
		$this->_param['cancel_url']=$cancel_url;
		return $this;
	}
	public function setGoods(array $ids){
		$this->_param['good_ids']=$ids;
		return $this;
	}
	public function asArray(){
		$param=$this->_param;
		$param['money']=strval($param['money']);
		$param['currency']=$param['money']->getCurrency();
		return $param;
	}
	public function serialize () {
		$money=$this->_param['money'];
		unset($this->_param['money']);
		$data=$this->_param;
		$data['money']=$money->getMoney();
		$data['currency']=$money->getCurrency();
		return json_encode($data);
	}
	public function unserialize ($serialized) {
		$data=json_decode($serialized,true);
		if (isset($data['currency']))$data['currency']=Money::CNY;
		$data['money']=Money::factroy($data['money'],$data['currency']);
		unset($data['currency']);
		$this->_param=$data;
	}
}