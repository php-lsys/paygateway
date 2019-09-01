<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Upacp;
class ApplePayMgr implements \LSYS\PayGateway\Mgr\PayAdapter,\LSYS\PayGateway\Mgr\RefundAdapter{
    protected $_name;
    protected $_callback;
    public function __construct($name){
        $this->_name=$name;
    }
    public function getName(){
        return $this->_name;
    }
    public function refundCreate($config){
        \LSYS\PayGateway\Utils::checkKeys($config,['merid','sign_cert','sign_pwd','verify_cert_dir','refund_apple_notify_url']);
        $config_=\LSYS\PayGateway\Adapter\Upacp\RefundConfig::arr($config);
        $config_->setNotifyUrl($config['refund_apple_notify_url']);
        return new \LSYS\PayGateway\Adapter\Upacp\AppleRefund($config_);
    }
    public function payMoreKey(){
        return null;
    }
    public function payCreate($config){
        \LSYS\PayGateway\Utils::checkKeys($config,['partner','key','private_key_path','ali_public_key_path','refund_notify_url']);
        $config_=\LSYS\PayGateway\Adapter\Upacp\PayConfig::arr($config);
        $config_->setNotifyUrl($config['pay_apple_notify_url']);
        return new \LSYS\PayGateway\Adapter\Upacp\ApplePay($config_);
    }
    public function supportType($type){
        return \LSYS\PayGateway\Adapter\Upacp\ApplePay::supportType($type);
    }
}




