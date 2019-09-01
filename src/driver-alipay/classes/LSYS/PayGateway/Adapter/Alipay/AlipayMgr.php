<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Alipay;
abstract class AlipayMgr implements \LSYS\PayGateway\Mgr\PayAdapter,\LSYS\PayGateway\Mgr\RefundAdapter{
    protected $_name;
    public function __construct($name){
        $this->_name=$name;
    }
    public function getName(){
        return $this->_name;
    }
    public function refundCreate($config){
        \LSYS\PayGateway\Utils::checkKeys($config,['partner','key','refund_notify_url']);
        $config['sign_type']='md5';
        $config_=\LSYS\PayGateway\Adapter\Alipay\RefundConfig::arr($config);
        $config_->setNotifyUrl($config['refund_notify_url']);
        return new \LSYS\PayGateway\Adapter\Alipay\Refund($config_);
    }
    public function payMoreKey(){
        return null;
    }
}




