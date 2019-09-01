<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Qpay;

use LSYS\PayGateway\Pay\PayParam;
use LSYS\PayGateway\Exception;
class PayApp extends PayNotify{
	/**
	 * @var Config
	 */
	protected $_config;
	public function __construct(Config $config){
		$this->_config=$config;
	}
	public static function supportType($type){
	    return $type&(self::TYPE_ANDROID|self::TYPE_IOS);
	}
	public function payRender(PayParam $pay_param){
		throw new Exception('not support the method');
	}
}