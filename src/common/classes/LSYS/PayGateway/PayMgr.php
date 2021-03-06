<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway;
use LSYS\PayGateway\Pay\PayAdapter;
class PayMgr{
    /**
     * @var \LSYS\PayGateway\Mgr\PayAdapter[]
     */
    protected $_pay=[];
    /**
     * @param \LSYS\PayGateway\Mgr\PayAdapter $pay
     * @return $this
     */
    public function add(\LSYS\PayGateway\Mgr\PayAdapter $pay){
        $this->_pay[]=$pay;
        return $this;
    }
    /**
     * @param string $name
     * @return \LSYS\PayGateway\Mgr\PayAdapter||null
     */
    public function find($name){
        foreach ($this->_pay as $v){
            if ($name==$v->getName())return $v;
        }
        return null;
    }
    /**
     * @param string $type
     * @return \LSYS\PayGateway\Mgr\PayAdapter[]
     */
    public function findAll($type=null){
        if($type==null) return $this->_pay;
        $out=array();
        foreach ($this->_pay as $v){
            if($v->supportType($type))$out[]=$v;
        }
        return $out;
    }
    public function supportType(PayAdapter $pay,$type){
        $types=[];
        foreach (array(
            PayAdapter::TYPE_ANDROID,
            PayAdapter::TYPE_IOS,
            PayAdapter::TYPE_PC,
            PayAdapter::TYPE_WAP,
            PayAdapter::TYPE_WECHAT,
        ) as $type){
            if($pay->supportType($type))$types[]=$type;
        }
        return $types;
    }
    /**
     * @param string $name
     * @return \LSYS\PayGateway\Mgr\RefundAdapter||NULL
     */
    public function findRefund($name){
        foreach ($this->_pay as $v){
            if ($v instanceof \LSYS\PayGateway\Mgr\RefundAdapter&&$v->getName()==$name){
                return $v;
            }
        }
        return null;
    }
}