<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Alipay;
class PayWapMgr extends AlipayMgr{
    public function payCreate($config){
        \LSYS\PayGateway\Utils::checkKeys($config,['partner','key','private_key_path','ali_public_key_path','seller_email','pay_wap_notify_url','pay_wap_return_url']);
        $config['sign_type']='rsa';
        $config_=PayConfig::arr($config);
        $config_->setSellerId($config['seller_email']);
        $config_->setNotifyUrl($config['pay_wap_notify_url']);
        $config_->setReturnUrl($config['pay_wap_return_url']);
        return new PayWap($config_);
    }
    public function supportType($type){
        return PayWap::supportType($type);
    }
}




