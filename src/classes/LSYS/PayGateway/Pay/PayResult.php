<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Pay;
use LSYS\PayGateway\Result;
abstract class PayResult extends Result{
    protected $_pay_no;
    protected $_pay_sn;
    protected $_pay_account;
    protected $_money;
    public function __construct($raw,$pay_sn,$pay_no=null){
        parent::__construct($raw);
        $this->_pay_sn=$pay_sn;
        $this->_pay_no=$pay_no;
    }
    public function setMoney($money){
        $this->_money= Money::factroy($money);
        return $this;
    }
    public function setPayAccount($pay_account){
        $this->_pay_account=$pay_account;
        return $this;
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
    /**
     * @return Money
     */
    public function getMoney(){
        return $this->_money;
    }
    public function getPayAccount(){
        return $this->_pay_account;
    }
}