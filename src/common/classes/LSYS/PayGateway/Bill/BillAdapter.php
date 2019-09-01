<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Bill;
interface BillAdapter{
	/**
	 * setting get bill date 
	 * @param string $date
	 * @return $this
	 */
	public function setDate($date);
	/**
	 * run get bill
	 */
	public function exec();
}