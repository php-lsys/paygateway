<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Alipay;
class PayAppMgr extends AlipayMgr{
    public function payCreate($config){
        \LSYS\PayGateway\Utils::checkKeys($config,['partner','key','private_key_path','ali_public_key_path','pay_app_notify_url']);
        $config['sign_type']='md5';
        $config_=PayConfig::arr($config);
        $config_->setNotifyUrl($config['pay_app_notify_url']);
        return  new PayApp($config_);
    }
    public function supportType($type){
        return PayApp::supportType($type);
    }
}




