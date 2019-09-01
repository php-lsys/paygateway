<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Alipay;
class PayWebMgr extends AlipayMgr{
    public function payCreate($config){
        \LSYS\PayGateway\Utils::checkKeys($config,['partner','key','seller_email','pay_pc_notify_url','pay_pc_return_url']);
        $config['private_key_path']=null;
        $config['ali_public_key_path']=null;
        $config['sign_type']='md5';
        $config_=PayConfig::arr($config);
        $config_->setSellerId($config['seller_email']);
        $config_->setNotifyUrl($config['pay_pc_notify_url']);
        $config_->setReturnUrl($config['pay_pc_return_url']);
        return new PayWeb($config_);
    }
    public function supportType($type){
        return PayWeb::supportType($type);
    }
}




