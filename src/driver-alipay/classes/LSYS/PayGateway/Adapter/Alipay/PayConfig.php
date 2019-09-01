<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Alipay;
class PayConfig extends Config{
	protected $_seller_id;
	protected $_notify_url;
	protected $_return_url;
	public function setSellerId($email){
		$this->_seller_id=$email;
		return $this;
	}
	public function setNotifyUrl($url){
		$this->_notify_url=$url;
		return $this;
	}
	public function setReturnUrl($url){
		$this->_return_url=$url;
		return $this;
	}
	public function getNotifyUrl(){
		return $this->_notify_url;
	}
	public function getReturnUrl(){
		return $this->_return_url;
	}
	public function getSellerId(){
		return $this->_seller_id;
	}
}