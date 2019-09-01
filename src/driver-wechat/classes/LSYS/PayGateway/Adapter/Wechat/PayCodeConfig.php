<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Wechat;
class PayCodeConfig extends Config{
	protected $_notify_url;
	protected $_qrcode_url;
	protected $_return_url;
	protected $_check_url;
	
	public function getAppid(){
		return $this->_appid;
	}
	
	public function setNotifyUrl($url){
		$this->_notify_url=$url;
		return $this;
	}
	public function setQrcodeUrl($url){
		$this->_qrcode_url=$url;
		return $this;
	}
	public function setReturnUrl($url){
		$this->_return_url=$url;
		return $this;
	}
	public function setCheckUrl($url){
		$this->_check_url=$url;
		return $this;
	}
	public function getNotifyUrl(){
		return $this->_notify_url;
	}
	public function getQrcodeUrl(){
		return $this->_qrcode_url;
	}
	public function getReturnUrl(){
		return $this->_return_url;
	}
	public function getCheckUrl(){
		return $this->_check_url;
	}
}
