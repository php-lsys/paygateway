<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Pay;
use LSYS\PayGateway\Result;
abstract class RefundResult extends Result{
    protected $_refund_no;
    protected $_refund_pay_no;
    public function __construct($raw,$refund_no,$refund_pay_no=null){
        parent::__construct($raw);
        $this->_refund_no=$refund_no;
        $this->_refund_pay_no=$refund_pay_no;
    }
    public function get_refund_no(){
        return $this->_refund_no;
    }
    public function get_refund_pay_no(){
        return $this->_refund_pay_no;
    }
}