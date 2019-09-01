<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\JD;
abstract class JDMgr implements \LSYS\PayGateway\Mgr\PayAdapter,\LSYS\PayGateway\Mgr\RefundAdapter{
    protected $_name;
    protected $_callback;
    public function __construct($name){
        $this->_name=$name;
    }
    public function getName(){
        return $this->_name;
    }
    public function refundCreate($config){
        \LSYS\PayGateway\Utils::checkKeys($config,['merchantNum','desKey','private_key','public_key','device','refund_notify_url']);
        $config_=\LSYS\PayGateway\Adapter\JD\RefundConfig::arr($config);
        $config_->setNotifyUrl($config['refund_notify_url']);
        return new \LSYS\PayGateway\Adapter\JD\Refund($config_);
    }
    public function payMoreKey(){
        return null;
    }
}




