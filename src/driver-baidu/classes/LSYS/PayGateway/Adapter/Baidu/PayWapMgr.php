<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Baidu;
class PayWapMgr extends BaiduMgr{
    public function payCreate($config){
        \LSYS\PayGateway\Utils::checkKeys($config,['sp_no','key_file','pay_wap_notify_url','pay_wap_return_url']);
        $config_=\LSYS\PayGateway\Adapter\Baidu\PayConfig::arr($config);
        $config_->setNotifyUrl($config['pay_wap_notify_url']);
        $config_->setReturnUrl($config['pay_wap_return_url']);
        return new \LSYS\PayGateway\Adapter\Baidu\PayWap($config_);
    }
    public function supportType($type){
        return \LSYS\PayGateway\Adapter\Baidu\PayWap::supportType($type);
    }
}




