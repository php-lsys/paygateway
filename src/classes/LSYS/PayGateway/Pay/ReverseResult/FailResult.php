<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Pay\ReverseResult;
use LSYS\PayGateway\Pay\ReverseResult;
class FailResult extends ReverseResult{
    protected $_is_local_fail;
    protected $_is_sign_fail;
    protected $_msg;
    public function __construct($raw,$msg=null,$pay_sn=null,$pay_no=null){
        parent::__construct($raw,$pay_sn,$pay_no);
        $this->_msg=$msg;
    }
    public function set_local_fail(){
        $this->_is_local_fail=true;
        return $this;
    }
    public function is_local_fail(){
        return $this->_is_local_fail;
    }
    public function set_sign_fail(){
        $this->_is_local_fail=true;
        $this->_is_sign_fail=true;
        return $this;
    }
    public function is_sign_fail(){
        return $this->_is_sign_fail;
    }
    public function get_msg(){
        return $this->_msg;
    }
}