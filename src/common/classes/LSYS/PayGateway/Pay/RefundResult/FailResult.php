<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Pay\RefundResult;
use LSYS\PayGateway\Pay\RefundResult;
class FailResult extends RefundResult{
    protected $_is_local_fail;
    protected $_is_sign_fail;
    protected $_is_local_rollback;
    protected $_msg;
    public function __construct($raw,$msg=null,$refund_no=null,$refund_pay_no=null){
        parent::__construct($raw,$refund_no,$refund_pay_no);
        $this->_msg=$msg;
    }
    /**
     * 设置建议本地回滚
     * @return \LSYS\PayGateway\Pay\RefundResult\FailResult
     */
    public function setLocalRollback(){
        $this->_is_local_rollback=true;
        return $this;
    }
    public function setLocalFail(){
        $this->_is_local_fail=true;
        return $this;
    }
    public function setSignFail(){
        $this->_is_local_fail=true;
        $this->_is_sign_fail=true;
        return $this;
    }
    /**
     * 是否建议本地回滚操作
     * @return bool
     */
    public function isLocalRollback(){
        return $this->_is_local_rollback;
    }
    /**
     * 返回true表示错误非由支付端告知的错误
     * @return boolean
     */
    public function isLocalFail(){
        return $this->_is_local_fail;
    }
    /**
     * 是否是否由签名不通过触发
     * @return boolean
     */
    public function isSignFail(){
        return $this->_is_sign_fail;
    }
    public function getMsg(){
        return $this->_msg;
    }
}