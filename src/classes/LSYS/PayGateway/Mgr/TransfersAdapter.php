<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Mgr;
interface TransfersAdapter{
    /**
     * @param array $config
     * @return \LSYS\PayGateway\Transfers\TransfersAdapter
     */
    public function transfers_create($config);
    /**
     * @return string
     */
    public function get_name();
    /**
     * @return string
     */
    public function transfers_type();
}