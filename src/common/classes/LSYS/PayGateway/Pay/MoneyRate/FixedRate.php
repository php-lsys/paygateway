<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Pay\MoneyRate;
use LSYS\PayGateway\Pay\Money;
use LSYS\PayGateway\Pay\MoneyRate;
use LSYS\PayGateway\Exception;
class FixedRate implements MoneyRate{
	public function exchangeRate($currency1,$currency2){
		if($currency1==Money::CNY&&$currency2==Money::USD){
			return 7/1;
		}elseif($currency1==Money::USD&&$currency2==Money::CNY){
			return 1/7;
		}
		throw new Exception('not suport the currency charge');
	}
}