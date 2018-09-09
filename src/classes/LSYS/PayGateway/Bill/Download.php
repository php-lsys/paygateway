<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Bill;
use LSYS\PayGateway\Bill\Downloader\CURL;
abstract class Download{
	protected $_downloader;
	protected $_tag;
	/**
	 * @param Downloader $downloader
	 * @return bool
	 */
	public function set_downloader(Downloader $downloader){
		$this->_downloader=$downloader;
		return $this;
	}
	/**
	 * @return Downloader
	 */
	public function get_downloader(){
		if ($this->_downloader==null)$this->_downloader=new CURL();
		return $this->_downloader;
	}
	/**
	 * @return DataFile
	 */
	abstract public function get_data_file();
	/**
	 * @return string
	 */
	public function get_tag(){
		return $this->_tag;
	}
	/**
	 * set tag name
	 * @param string $tag
	 * @return \LSYS\PayGateway\Bill\Download
	 */
	public function set_tag($tag){
		$this->_tag=$tag;
		return $this;
	}
	/**
	 * 从\LSYS\PayGateway\Bill\Download解析结果,完成时清理废数据
	 * @param \LSYS\PayGateway\Bill\Download $bill
	 * @return \LSYS\PayGateway\Bill\Result||null
	 */
	public static function fetch(\LSYS\PayGateway\Bill\Download $bill){
		$result=$bill->get_data_file()->get_result();
		//遍历完成清除下载文件
		if ($result == false) $bill->get_downloader()->delete(get_class($bill), $bill->get_tag());
		return $result;
	}
}