<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Pay\PayResult;
use LSYS\PayGateway\Pay\PayResult;
class SuccResult extends PayResult{
    protected $_refunded=null;
    public function __construct($raw,$pay_sn,$pay_no){
        parent::__construct($raw,$pay_sn,$pay_no);
    }
    public function set_refundd($is_refund){
        $this->_refunded=boolval($is_refund);
        return $this;
    }
    public function get_refundd(){
        return $this->_refunded;
    }
}