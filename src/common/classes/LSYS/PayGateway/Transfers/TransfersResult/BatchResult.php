<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Transfers\TransfersResult;
use LSYS\PayGateway\Transfers\TransfersResult;
class BatchResult extends \LSYS\PayGateway\Result implements \Countable,\SeekableIterator{
    protected $_result  = [];
    protected $_total_rows  = 0;
    protected $_current_row = 0;
    /**
     * @param mixed $raw
     * @param TransfersResult[] $transfers
     */
    public function __construct($raw,array $transfers){
        parent::__construct($raw);
        $this->_result=$transfers;
        $this->_total_rows=count($this->_result);
    }
    public function count()
    {
        return $this->_total_rows;
    }
    public function key()
    {
        return $this->_current_row;
    }
    public function next()
    {
        ++$this->_current_row;
        return $this;
    }
    public function prev()
    {
        --$this->_current_row;
        return $this;
    }
    public function rewind()
    {
        $this->_current_row = 0;
        return $this;
    }
    public function valid()
    {
        return $this->_current_row >= 0 AND $this->_current_row < $this->_total_rows;
    }
    public function seek($offset)
    {
        if ($offset < 0 OR $offset >= $this->_total_rows)
        {
            return false;
        }
        $this->_current_row = $offset;
    }
    /**
     * 得到一个TransfersResult结果
     * @return IngResult|SuccResult
     */
    public function current(){
        if (!$this->valid()||!isset($this->_result[$this->_current_row])) return null;
        return $this->_result[$this->_current_row];
    }
}