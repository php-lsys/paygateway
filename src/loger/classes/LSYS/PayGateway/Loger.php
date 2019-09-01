<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway;
use LSYS\PayGateway\Loger\Storage;
class Loger{
    const FILTER_PAY_RESULT=1<<0;
    const FILTER_REFUND_RESULT=1<<1;
    const FILTER_REVERSE_RESULT=1<<2;
    const FILTER_TRANSFERS_RESULT=1<<3;
    const LEVEL_RESULT_SUCCESS=1<<0;
    const LEVEL_RESULT_ING=1<<1;
    const LEVEL_RESULT_FAIL=1<<2;
    /**
     * @var Storage[]
     */
    protected $_storage=[];
    public function addStorage(Storage $storage){
        $this->_storage[]=$storage;
        return $this;
    }
    public function delStorage(Storage $storage){
        foreach ($this->_storage as $k=>$v){
            if ($v===$storage)unset($this->_storage[$k]);
        }
        return $this;
    }
    public function clearStorage(){
        $this->_storage=[];
        return $this;
    }
    public function add($token,Result $result){
        $data=array(
            self::FILTER_PAY_RESULT=>array(
                \LSYS\PayGateway\Pay\PayResult\SuccResult::class=>self::LEVEL_RESULT_SUCCESS,
                \LSYS\PayGateway\Pay\PayResult\IngResult::class=>self::LEVEL_RESULT_ING,
                \LSYS\PayGateway\Pay\PayResult\FailResult::class=>self::LEVEL_RESULT_FAIL
           ),
            self::FILTER_REFUND_RESULT=>array(
                \LSYS\PayGateway\Pay\RefundResult\SuccResult::class=>self::LEVEL_RESULT_SUCCESS,
                \LSYS\PayGateway\Pay\RefundResult\IngResult::class=>self::LEVEL_RESULT_ING,
                \LSYS\PayGateway\Pay\RefundResult\FailResult::class=>self::LEVEL_RESULT_FAIL
           ),
            self::FILTER_REVERSE_RESULT=>array(
                \LSYS\PayGateway\Pay\ReverseResult\SuccResult::class=>self::LEVEL_RESULT_SUCCESS,
                \LSYS\PayGateway\Pay\ReverseResult\IngResult::class=>self::LEVEL_RESULT_ING,
                \LSYS\PayGateway\Pay\ReverseResult\FailResult::class=>self::LEVEL_RESULT_FAIL
           ),
            self::FILTER_TRANSFERS_RESULT=>array(
                \LSYS\PayGateway\Transfers\TransfersResult\BatchResult::class=>self::LEVEL_RESULT_SUCCESS,
                \LSYS\PayGateway\Transfers\TransfersResult\SuccResult::class=>self::LEVEL_RESULT_SUCCESS,
                \LSYS\PayGateway\Transfers\TransfersResult\IngResult::class=>self::LEVEL_RESULT_ING,
                \LSYS\PayGateway\Transfers\TransfersResult\FailResult::class=>self::LEVEL_RESULT_FAIL
           ),
        );
        foreach ($this->_storage as $storage){
            foreach ($data as $filter=>$val){
                if (!($storage->getFilter()&$filter))continue;
                foreach ($val as $class=>$level){
                    if (!$result instanceof $class
                        ||!($storage->getFilterLevel($filter)&$level)
                    )continue;
                    if(method_exists($result, 'isSignFail')
                        &&$storage->ignoreFailSign()
                        &&$result->isSignFail()
                    )continue;
                    $storage->loger($token,$result);
                }
            }
        }
        return $this;
    }
}