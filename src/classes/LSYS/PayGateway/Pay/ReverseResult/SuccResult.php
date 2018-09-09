<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Pay\ReverseResult;
use LSYS\PayGateway\Pay\ReverseResult;
class SuccResult extends ReverseResult{
    public function __construct($raw,$pay_sn,$pay_no){
        parent::__construct($raw,$pay_sn,$pay_no);
    }
}