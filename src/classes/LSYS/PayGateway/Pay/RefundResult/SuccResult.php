<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Pay\RefundResult;
use LSYS\PayGateway\Pay\RefundResult;
class SuccResult extends RefundResult{
    public function __construct($raw,$refund_no,$refund_pay_no){
        parent::__construct($raw,$refund_no,$refund_pay_no);
    }
}