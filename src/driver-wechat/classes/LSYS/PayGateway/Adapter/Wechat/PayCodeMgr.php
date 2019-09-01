<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Wechat;
class PayCodeMgr extends WechatMgr{
    public function payCreate($config){
        \LSYS\PayGateway\Utils::checkKeys($config,['APPID','MCHID','KEY','APPSECRET',
            'SSLCERT_PATH','SSLKEY_PATH','SSLCERT_CA','pay_code_notify_url',
            'pay_code_qrcode_url','pay_code_check_url','pay_code_return_url',
        ]);
        $config_=\LSYS\PayGateway\Adapter\Wechat\PayCodeConfig::arr($config);
        $config_->setNotifyUrl($config['pay_code_notify_url']);
        $config_->setQrcodeUrl($config['pay_code_qrcode_url']);
        $config_->setCheckUrl($config['pay_code_check_url']);
        $config_->setReturnUrl($config['pay_code_return_url']);
        return new \LSYS\PayGateway\Adapter\Wechat\PayCode($config_);
    }
    public function supportType($type){
        return \LSYS\PayGateway\Adapter\Wechat\PayCode::supportType($type);
    }
}




