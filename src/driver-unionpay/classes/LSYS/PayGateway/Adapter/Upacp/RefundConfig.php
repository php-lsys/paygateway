<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Upacp;
class RefundConfig extends Config{
	protected $_notify_url;
	public function setNotifyUrl($url){
		$this->_notify_url=$url;
		return $this;
	}
	public function getNotifyUrl(){
		return $this->_notify_url;
	}
}