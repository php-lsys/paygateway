<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Palpay;
class DirectPayMgr extends PalpayMgr{
    public function payCreate($config){
        \LSYS\PayGateway\Utils::checkKeys($config,['username','password','signature','pay_ipn_url','pay_do_url']);
        $config_=\LSYS\PayGateway\Adapter\Palpay\DirectPayConfig::arr($config);
        $config_->setNotifyUrl($config['pay_ipn_url']);
        $config_->setPayUrl($config['pay_do_url']);
        return new \LSYS\PayGateway\Adapter\Palpay\DirectPay($config_);
    }
    public function supportType($type){
        return \LSYS\PayGateway\Adapter\Palpay\DirectPay::supportType($type);
    }
}




