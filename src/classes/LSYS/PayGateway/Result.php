<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway;
abstract class Result{
    private $_raw;
    private $_param;
    public function __construct($raw){
        $this->_raw=$raw;
    }
    public function getRaw(){
        return $this->_raw;
    }
    public function __toString(){
        $raw=$this->_raw;
        if (is_array($raw))$raw=json_encode($raw);
        if(is_scalar($raw))return strval($raw);
        return print_r($raw,true);
    }
    /**
     * 设置解析够的数据
     * @param mixed $param
     * @return static
     */
    public function setParam($param){
        $this->_param=$param;
        return $this;
    }
    /**
     * 获取解析后的数据
     * @return mixed
     */
    public function getParam(){
        return $this->_param;
    }
}