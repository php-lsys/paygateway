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
    public function transfersCreate($config);
    /**
     * @return string
     */
    public function getName();
    /**
     * @return string
     */
    public function transfersType();
}