<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Pay;
interface PayAdapter{
    /**
     * pc page
     * @var integer
     */
    const TYPE_PC=1<<0;
    /**
     * mobile wap page
     * @var integer
     */
    const TYPE_WAP=1<<1;
    /**
     * android app
     * @var integer
     */
    const TYPE_ANDROID=1<<2;
    /**
     * ios app
     * @var integer
     */
    const TYPE_IOS=1<<3;
    /**
     * wexin
     * @var integer
     */
    const TYPE_WECHAT=1<<4;
    /**
     * @return int
     */
    public static function supportType($type);
}