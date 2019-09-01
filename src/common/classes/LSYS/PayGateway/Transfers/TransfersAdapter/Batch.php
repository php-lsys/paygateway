<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Transfers\TransfersAdapter;
use LSYS\PayGateway\Transfers\TransfersParam;
use LSYS\PayGateway\Transfers\TransfersAdapter;

interface Batch extends TransfersAdapter{
	public function add(TransfersParam $param);
	public function render();
}