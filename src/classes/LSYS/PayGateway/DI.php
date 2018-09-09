<?php
namespace LSYS\PayGateway;
/**
 * @method \LSYS\PayGateway\PayMgr paygateway_paymgr()
 * @method \LSYS\PayGateway\TransfersMgr paygateway_transfersmgr()
 */
class DI extends \LSYS\DI{
    /**
     * @return static
     */
    public static function get(){
        $di=parent::get();
        !isset($di->paygateway_paymgr)&&$di->paygateway_paymgr(new \LSYS\DI\SingletonCallback(function (){
            return new \LSYS\PayGateway\PayMgr();
        }));
        !isset($di->paygateway_transfersmgr)&&$di->paygateway_transfersmgr(new \LSYS\DI\SingletonCallback(function (){
            return new \LSYS\PayGateway\TransfersMgr();
        }));
        return $di;
    }
}