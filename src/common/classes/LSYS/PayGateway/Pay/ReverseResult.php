<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Pay;
use LSYS\PayGateway\Result;
abstract class ReverseResult extends Result{
    protected $_pay_no;
    protected $_pay_sn;
    public function __construct($raw,$pay_sn,$pay_no=null){
        parent::__construct($raw);
        $this->_pay_sn=$pay_sn;
        $this->_pay_no=$pay_no;
    }
    /**
     * 外部支付系统订单号
     * @return string
     */
    public function getPayNo(){
        return $this->_pay_no;
    }
    /**
     * 本地站点订单号
     * @return string
     */
    public function getPaySn(){
        return $this->_pay_sn;
    }
}