<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway;
use LSYS\PayGateway\Mgr\TransfersAdapter;
class TransfersMgr{
    /**
     * @var TransfersAdapter[]
     */
    protected $_transfers=[];
    public function add(TransfersAdapter $refund){
        $this->_transfers[]=$refund;
        return $this;
    }
    /**
     * @param string $name
     * @return TransfersAdapter||null
     */
    public function find($name){
        foreach ($this->_transfers as $v){
            if ($name==$v->getName())return $v;
        }
        return null;
    }
    /**
     * @param string $name
     * @return string
     */
    public function transfersType($name){
        foreach ($this->_transfers as $vv){
            if($vv->getName()==$name){
                return $vv->transfersType();
            }
        }
    }
    /**
     * @return \LSYS\PayGateway\Mgr\TransfersAdapter[]
     */
    public function findAll(){
        return $this->_transfers;
    }
}


