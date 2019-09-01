<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Upacp;
class PayMgr implements \LSYS\PayGateway\Mgr\PayAdapter,\LSYS\PayGateway\Mgr\RefundAdapter{
    protected $_name;
    public function __construct($name){
        $this->_name=$name;
    }
    public function getName(){
        return $this->_name;
    }
    public function refundCreate($config){
        \LSYS\PayGateway\Utils::checkKeys($config,['partner','key','private_key_path','ali_public_key_path','refund_notify_url']);
        $config_=\LSYS\PayGateway\Adapter\Upacp\RefundConfig::arr($config);
        $config_->setNotifyUrl($config['refund_notify_url']);
        return new \LSYS\PayGateway\Adapter\Upacp\Refund($config_);
    }
    public function payMoreKey(){
        return null;
    }
    public function payCreate($config){
        \LSYS\PayGateway\Utils::checkKeys($config,['merid','sign_cert','sign_pwd','verify_cert_dir','pay_notify_url','pay_return_url']);
        $config_=\LSYS\PayGateway\Adapter\Upacp\PayConfig::arr($config);
        $config_->setNotifyUrl($config['pay_notify_url']);
        $config_->setReturnUrl($config['pay_return_url']);
        return new \LSYS\PayGateway\Adapter\Upacp\Pay($config_);
    }
    public function supportType($type){
        return \LSYS\PayGateway\Adapter\Upacp\Pay::supportType($type);
    }
}




