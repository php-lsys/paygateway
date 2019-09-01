<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Pay;
interface PayAdapterNotify{
	/**
	 * pay notify
	 * @return PayResult
	 */
	public function payNotify();
	/**
	 * pay notify
	 */
	public function payNotifyOutput($status=true,$msg=null);
}