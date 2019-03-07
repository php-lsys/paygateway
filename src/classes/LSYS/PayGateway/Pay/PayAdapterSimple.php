<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Pay;
interface PayAdapterSimple extends PayAdapter{
	/**
	 * render to pay
	 * @param PayParam $pay_param
	 * @return PayRender
	 */
	public function payRender(PayParam $pay_param);
}