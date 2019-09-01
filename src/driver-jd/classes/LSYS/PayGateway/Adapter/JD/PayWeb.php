<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\JD;
use LSYS\PayGateway\Pay\PayAdapterCallback;

class PayWeb extends JD implements PayAdapterCallback{
	public function __construct(PayConfig $config){
		parent::__construct($config);
		$this->_pay_url='https://wepay.jd.com/jdpay/saveOrder';
	}
	public static function supportType($type){
	    return $type&self::TYPE_PC;
	}
}