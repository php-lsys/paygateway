<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Transfers;
interface TransfersNotify{
	/**
	 * @return TransfersResult|BatchResult
	 */
	public function transfersNotify();
	/**
	 * @param bool $status
	 * @param string $msg
	 */
	public function transfersNotifyOutput($status=true,$msg=null);
}