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
            if ($name==$v->get_name())return $v;
        }
        return null;
    }
    /**
     * @param string $name
     * @return string
     */
    public function transfers_type($name){
        foreach ($this->_transfers as $vv){
            if($vv->get_name()==$name){
                return $vv->transfers_type();
            }
        }
    }
    /**
     * @return \LSYS\PayGateway\Mgr\TransfersAdapter[]
     */
    public function find_all(){
        return $this->_transfers;
    }
}


