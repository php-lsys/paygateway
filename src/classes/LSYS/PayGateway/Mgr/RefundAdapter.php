<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Mgr;
interface RefundAdapter{
    /**
     * @param array $config
     * @return \LSYS\PayGateway\Pay\RefundAdapter
     */
    public function refund_create($config);
}