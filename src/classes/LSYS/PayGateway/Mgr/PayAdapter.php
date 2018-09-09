<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Mgr;
interface PayAdapter{
    /**
     * @param array $config
     * @return \LSYS\PayGateway\Pay\PayAdapter|\LSYS\PayGateway\Pay\PayAdapterSimple|\LSYS\PayGateway\Pay\PayAdapterMore
     */
    public function pay_create($config);
	/**
	 * @return string
	 */
	public function get_name();
	/**
	 * @return string||array
	 */
	public function pay_more_key();
	/**
	 * @param string $type
	 * @return bool
	 */
	public function support_type($type);
}