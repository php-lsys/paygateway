<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\JD;
class PayWapMgr extends JDMgr{
    public function payCreate($config){
        \LSYS\PayGateway\Utils::checkKeys($config,['merchantNum','desKey','private_key','public_key','device','pay_wap_notify_url','pay_wap_return_url']);
        $config_=\LSYS\PayGateway\Adapter\JD\PayConfig::arr($config);
        $config_->setNotifyUrl($config['pay_wap_notify_url']);
        $config_->setReturnUrl($config['pay_wap_return_url']);
        return new \LSYS\PayGateway\Adapter\JD\PayWap($config_);
    }
    public function supportType($type){
        return \LSYS\PayGateway\Adapter\JD\PayWap::supportType($type);
    }
}




