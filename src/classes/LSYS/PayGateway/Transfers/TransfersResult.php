<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Transfers;
use LSYS\PayGateway\Result;
abstract class TransfersResult extends Result{
    protected $_transfers_no;
    protected $_pay_no;
    public function __construct($raw,$transfers_no,$pay_no=null){
        parent::__construct($raw);
        $this->_transfers_no=$transfers_no;
        $this->_pay_sn=$pay_no;
    }
    public function get_transfers_no(){
        return $this->_transfers_no;
    }
    public function get_pay_no(){
        return $this->_pay_no;
    }
}