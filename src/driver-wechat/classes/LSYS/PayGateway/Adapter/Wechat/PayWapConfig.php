<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Wechat;
class PayWapConfig extends Config{
	protected $_notify_url;
	protected $_return_url;
	protected $_oauth_return_url;
	public function setNotifyUrl($url){
		$this->_notify_url=$url;
		return $this;
	}
	public function setReturnUrl($url){
		$this->_return_url=$url;
		return $this;
	}
	public function setOauthReturnUrl($url){
		$this->_oauth_return_url=$url;
		return $this;
	}
	public function getNotifyUrl(){
		return $this->_notify_url;
	}
	public function getReturnUrl(){
		return $this->_return_url;
	}
	public function getOauthReturnUrl(){
		return $this->_oauth_return_url;
	}
}
