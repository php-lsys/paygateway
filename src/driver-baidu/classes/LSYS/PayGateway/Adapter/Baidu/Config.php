<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Baidu;
class Config{
	// 	return array(
	// 		'sp_no'=>'700000000000001',
	// 		'key_file'=>'XX.key',
	// 	);
	/**
	 * @param array $config
	 * @return static
	 */
	public static function arr(array $config){
		$self= new static(
			$config['sp_no'],
			$config['key_file']
			);
		return $self;
	}
	protected $_config=array();
	public function __construct($sp_no,$key_file){
		$c['sp_no']= $sp_no;
		$c['key_file']= $key_file;
		$this->_config=$c;
	}
	public function getSpNo(){
		return $this->_config['sp_no'];
	}
	public function getKeyFile(){
		return $this->_config['key_file'];
	}
}