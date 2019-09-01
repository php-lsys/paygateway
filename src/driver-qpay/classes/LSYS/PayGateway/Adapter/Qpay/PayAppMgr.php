<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Qpay;
class PayAppMgr extends QpayMgr{
    public function payCreate($config){
        \LSYS\PayGateway\Utils::checkKeys($config,['APPID','MCHID','KEY','pubAcc','pubAccHint','SSLCERT_PATH','SSLKEY_PATH']);
        $config_=\LSYS\PayGateway\Adapter\Qpay\Config::arr($config);
        return new \LSYS\PayGateway\Adapter\Qpay\PayApp($config_);
    }
    public function supportType($type){
        return \LSYS\PayGateway\Adapter\Qpay\PayApp::supportType($type);
    }
}




