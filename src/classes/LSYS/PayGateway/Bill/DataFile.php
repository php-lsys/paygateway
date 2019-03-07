<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Bill;
interface DataFile extends Data{
	/**
	 * @param string $file_path
	 * @return bool
	 */
	public function loadFile($file_path);
}