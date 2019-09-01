<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Wechat;
class PayAppMgr extends WechatMgr{
    public function payCreate($config){
        \LSYS\PayGateway\Utils::checkKeys($config,['APPID','MCHID','KEY','APPSECRET','SSLCERT_PATH','SSLKEY_PATH','SSLCERT_CA']);
        $config_=\LSYS\PayGateway\Adapter\Wechat\Config::arr($config);
        return new \LSYS\PayGateway\Adapter\Wechat\PayApp($config_);
    }
    public function supportType($type){
        return \LSYS\PayGateway\Adapter\Wechat\PayApp::supportType($type);
    }
}




