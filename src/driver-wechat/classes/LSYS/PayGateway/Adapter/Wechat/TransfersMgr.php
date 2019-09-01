<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Wechat;
use LSYS\PayGateway\Transfers\TransfersAdapter\RealTime;
class TransfersMgr implements \LSYS\PayGateway\Mgr\TransfersAdapter{
    protected $_name;
    public function __construct($name){
        $this->_name=$name;
    }
    public function getName(){
        return $this->_name;
    }
    public function transfersCreate($config){
        \LSYS\PayGateway\Utils::checkKeys($config,['APPID','MCHID','KEY','APPSECRET','SSLCERT_PATH','SSLKEY_PATH','SSLCERT_CA']);
        $config_=Config::arr($config);
        return new Transfers($config_);
    }
    public function transfersType(){
        return RealTime::class;
    }
}




