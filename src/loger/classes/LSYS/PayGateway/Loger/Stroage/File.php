<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Loger\Stroage;
use LSYS\PayGateway\Result;
use LSYS\PayGateway\Loger\Storage;
use LSYS\PayGateway\Loger;
class File implements Storage{
    protected $dir;
    protected $skip_sign_fail;
    protected $name_format;
    protected $_filter;
    protected $_level;
    public function __construct($dir,$skip_sign_fail=false,$name_format='Y-m-d'){
        $this->dir=rtrim($dir,"\\/")."/";
        $this->skip_sign_fail=$skip_sign_fail;
		$this->name_format=$name_format;
		$this->_filter=Loger::FILTER_PAY_RESULT|Loger::FILTER_REFUND_RESULT|Loger::FILTER_REVERSE_RESULT|Loger::FILTER_TRANSFERS_RESULT;
		$this->_level=array(
		    Loger::FILTER_PAY_RESULT=>$this->_filter,
		    Loger::FILTER_REFUND_RESULT=>$this->_filter,
		    Loger::FILTER_REVERSE_RESULT=>$this->_filter,
		    Loger::FILTER_TRANSFERS_RESULT=>$this->_filter,
		);
    }
    public function setFilterLevel($result_mark,$level_mark=0){
        $this->_level[$result_mark]=$level_mark;
        return $this;
    }
    public function getFilterLevel($result_mark){
        return isset($this->_level[$result_mark])?$this->_level[$result_mark]:0;
    }
    public function ignoreFailSign(){
        return $this->skip_sign_fail;
    }
    public function getFilter(){
        return $this->_filter;
    }
    public function loger($token,Result $result){
        if (!is_dir($this->dir)){
            if (!is_writable($this->dir))return false;
            @mkdir($this->dir);
        }
        $file=$this->dir.date($this->name_format).".log";
        return file_put_contents($file, $this->_format($token,$result),FILE_APPEND);
    }
    protected function _format($token,Result $result){
        $data=strval($result);
        return "{$token}:\r\n{$data}\r\n";
    }
}