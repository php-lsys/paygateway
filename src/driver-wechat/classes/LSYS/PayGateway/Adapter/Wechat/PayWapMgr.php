<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Wechat;
class PayWapMgr extends WechatMgr{
    public function payCreate($config){
        \LSYS\PayGateway\Utils::checkKeys($config,['APPID','MCHID','KEY','APPSECRET',
            'SSLCERT_PATH','SSLKEY_PATH','SSLCERT_CA','pay_wap_notify_url',
            'pay_wap_return_url','pay_oauth_return_url'
        ]);
        $config_=\LSYS\PayGateway\Adapter\Wechat\PayWapConfig::arr($config);
        $config_->setNotifyUrl($config['pay_wap_notify_url']);
        $config_->setReturnUrl($config['pay_wap_return_url']);
        $config_->setOauthReturnUrl($config['pay_oauth_return_url']);
        return new \LSYS\PayGateway\Adapter\Wechat\PayWap($config_);
    }
    public function supportType($type){
        return \LSYS\PayGateway\Adapter\Wechat\PayWap::supportType($type);
    }
}




