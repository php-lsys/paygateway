<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\JD;
class Config{
	// 	return array(
	// 		'merchantNum'=>'700000000000001',
	// 		'desKey'=>'8934e7d15453e97507ef794cf7b0519d',
	// 		'private_key'=>'pr.pem',
	// 		'public_key'=>'pu.pem',
	// 	);
	/**
	 * @param array $config
	 * @return static
	 */
	public static function arr(array $config){
		$self= new static(
			$config['merchantNum'],
			$config['desKey'],
			$config['private_key'],
			$config['public_key'],
			$config['device']
		);
		return $self;
	}
	protected $_config=array();
	public function __construct($merchant,$desKey,$private_key_path,$public_key_path,$device){
		$c['merchant']= $merchant;
		$c['desKey']= $desKey;
		$c['device']= $device;
		$c['private_key']= $private_key_path;
		$c['public_key']= $public_key_path;
		$this->_config=$c;
	}
	public function getDevice(){
		return $this->_config['device'];
	}
	public function getDeskey(){
		return $this->_config['desKey'];
	}
	public function getMerchant(){
		return $this->_config['merchant'];
	}
	public function getPrivateKeyPath(){
		return $this->_config['private_key'];
	}
	public function getPublicKeyPath(){
		return $this->_config['public_key'];
	}
}