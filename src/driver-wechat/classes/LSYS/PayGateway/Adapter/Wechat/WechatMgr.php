<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Wechat;
abstract class WechatMgr implements 
    \LSYS\PayGateway\Mgr\PayAdapter,
    \LSYS\PayGateway\Mgr\RefundAdapter{
    protected $_name;
    public function __construct($name){
        $this->_name=$name;
    }
    public function getName(){
        return $this->_name;
    }
    public function refundCreate($config){
        \LSYS\PayGateway\Utils::checkKeys($config,['APPID','MCHID','KEY','APPSECRET','SSLCERT_PATH','SSLKEY_PATH','SSLCERT_CA']);
        $config_=\LSYS\PayGateway\Adapter\Wechat\Config::arr($config);
        return new \LSYS\PayGateway\Adapter\Wechat\Refund($config_);
    }
    public function payMoreKey(){
        return null;
    }
}




