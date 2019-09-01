<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Transfers\TransfersResult;
use LSYS\PayGateway\Transfers\TransfersResult;
class SuccResult extends TransfersResult{
    public function __construct($raw,$transfers_no,$pay_no){
        parent::__construct($raw,$transfers_no,$pay_no);
    }
}