<?php
namespace LSYS\PayGateway;
/**
 * @method \LSYS\PayGateway\PayMgr payGatewayPayMgr()
 * @method \LSYS\PayGateway\TransfersMgr payGatewayTransfersMgr()
 */
class DI extends \LSYS\DI{
    /**
     * @return static
     */
    public static function get(){
        $di=parent::get();
        !isset($di->payGatewayPayMgr)&&$di->payGatewayPayMgr(new \LSYS\DI\SingletonCallback(function (){
            return new \LSYS\PayGateway\PayMgr();
        }));
        !isset($di->payGatewayTransfersMgr)&&$di->payGatewayTransfersMgr(new \LSYS\DI\SingletonCallback(function (){
            return new \LSYS\PayGateway\TransfersMgr();
        }));
        return $di;
    }
}