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
    public function payCreate($config);
	/**
	 * @return string
	 */
	public function getName();
	/**
	 * @return string||array
	 */
	public function payMoreKey();
	/**
	 * @param string $type
	 * @return bool
	 */
	public function supportType($type);
}