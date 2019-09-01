<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Alipay;
class TransfersConfig extends Config{
	protected $_notify_url;
	protected $_seller_id;
	protected $_seller_name;
	public function setNotifyUrl($url){
		$this->_notify_url=$url;
		return $this;
	}
	public function getNotifyUrl(){
		return $this->_notify_url;
	}
	public function setSellerId($email){
		$this->_seller_id=$email;
		return $this;
	}
	public function getSellerId(){
		return $this->_seller_id;
	}
	public function setSellerName($name){
		$this->_seller_name=$name;
		return $this;
	}
	public function getSellerName(){
		return $this->_seller_name;
	}
}