<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Pay;
use LSYS\PayGateway\Utils;
use LSYS\PayGateway\Pay\MoneyRate\FixedRate;
use LSYS\PayGateway\Exception;
class Money{
	protected static $_money_rate;
	public static function setMoneyRate(MoneyRate $money_rate){
		self::$_money_rate=$money_rate;
	}
	protected function _getMoneyRate(){
		if (self::$_money_rate==null)self::$_money_rate=new FixedRate();
		return self::$_money_rate;
	}
	public static function factroy($money,$currency=Money::CNY){
		if ($money instanceof Money) return $money;
		return new static($money, $currency);
	}
	//support currency
	const CNY='CNY';//人民币
	const USD='USD';//美刀
	const CAD='CAD';//加元
	const EUR='EUR';//欧元
	const JPY='JPY';//日元
	const HKD='HKD';//港币
	const TWD='TWD';//台币
	//....
	protected $_money;
	protected $_currency;
	public function __construct($money,$currency){
		$this->_money=$money;
		$this->_currency=$currency;
	}
	public function __toString(){
		return strval($this->getMoney());
	}
	public function to($currency){
		if ($currency==$this->_currency) return  Utils::moneyFormat($this->_money); 
		$rate=$this->_getMoneyRate();
		$rate=$rate->exchangeRate($currency,$this->_currency);
		if ($rate<=0) throw new Exception('exchange rate is wrong :'.$rate);
		return Utils::moneyFormat($this->_money*$rate);
	}
	public function getCurrency(){
		return $this->_currency;
	}
	public function getMoney(){
		return Utils::moneyFormat($this->_money);
	}
	public function equal(Money $money){
		if ($money->_currency!=$this->_currency) return Utils::moneyEqual($this->_money,$money->_money);
		//不同货币??
		return $this->_money/$money->_money>=0.8;//汇率问题...
	}
}