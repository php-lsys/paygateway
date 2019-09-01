<?php
namespace LSYS\PayGateway\Loger;
/**
 * @method \LSYS\PayGateway\Loger payGatewayLoger()
 */
class DI extends \LSYS\DI{
    /**
     * @return static
     */
    public static function get(){
        $di=parent::get();
        !isset($di->payGatewayLoger)&&$di->payGatewayLoger(new \LSYS\DI\SingletonCallback(function (){
            return new \LSYS\PayGateway\Loger();
        }));
        return $di;
    }
}