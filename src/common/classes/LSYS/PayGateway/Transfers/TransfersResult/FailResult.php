<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Transfers\TransfersResult;
use LSYS\PayGateway\Transfers\TransfersResult;
class FailResult extends TransfersResult{
    protected $_is_local_fail;
    protected $_is_sign_fail;
    protected $_is_local_rollback;
    protected $_msg;
    public function __construct($raw,$msg=null,$transfers_no=null,$pay_no=null){
        parent::__construct($raw,$transfers_no,$pay_no);
        $this->_msg=$msg;
    }
    /**
     * 设置建议本地回滚
     * @return $this
     */
    public function setLocalRollback(){
        $this->_is_local_rollback=true;
        return $this;
    }
    /**
     * 是否建议本地回滚操作
     * @return bool
     */
    public function isLocalRollback(){
        return $this->_is_local_rollback;
    }
    public function setLocalFail(){
        $this->_is_local_fail=true;
        return $this;
    }
    public function isLocalFail(){
        return $this->_is_local_fail;
    }
    public function setSignFail(){
        $this->_is_local_fail=true;
        $this->_is_sign_fail=true;
        return $this;
    }
    public function isSignFail(){
        return $this->_is_sign_fail;
    }
    public function getMsg(){
        return $this->_msg;
    }
}