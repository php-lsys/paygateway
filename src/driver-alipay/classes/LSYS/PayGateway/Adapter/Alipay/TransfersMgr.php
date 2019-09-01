<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Alipay;
use LSYS\PayGateway\Transfers\TransfersAdapter\Batch;
class TransfersMgr implements \LSYS\PayGateway\Mgr\TransfersAdapter{
    protected $_name;
    public function __construct($name){
        $this->_name=$name;
    }
    public function getName(){
        return $this->_name;
    }
    public function transfersCreate($config){
        \LSYS\PayGateway\Utils::checkKeys($config,['partner','key','transfers_notify_url','seller_name','seller_email']);
        $config['private_key_path']=null;
        $config['ali_public_key_path']=null;
        $config['sign_type']='md5';
        $config_=TransfersConfig::arr($config);
        $config_->setNotifyUrl($config['transfers_notify_url']);
        $config_->setSellerId($config['seller_email']);
        $config_->setSellerName($config['seller_name']);
        return new Transfers($config_);
    }
    public function transfersType(){
        return Batch::class;
    }
}




