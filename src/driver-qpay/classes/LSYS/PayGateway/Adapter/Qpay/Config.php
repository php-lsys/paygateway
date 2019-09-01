<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Qpay;
class Config {
	protected $appid;
	protected $mchid;
	protected $key;
	protected $pubAcc;
	protected $pubAccHint;
	protected $sslcert_path;
	protected $sslkey_path;
	protected $proxy_ip='0.0.0.0';
	protected $proxy_port='0';
	private static $cls=[];
	/**
	 * @param array $config
	 * @return static
	 */
	public static function arr(array $config){
	    $cls=get_called_class();
	    if (!isset(self::$cls[$cls])){
	        $clsobj= new static($config['APPID'], $config['MCHID'], $config['KEY'], $config['pubAcc'], $config['pubAccHint']);
	        isset($config['CURL_PROXY_HOST'])&&isset($config['CURL_PROXY_PORT'])&&$clsobj->setProxy($config['CURL_PROXY_HOST'],$config['CURL_PROXY_PORT']);
	        if (!isset($config['SSL_DIR']))$config['SSL_DIR']='';
	        else $config['SSL_DIR']=rtrim($config['SSL_DIR'],'\\/').'/';
	        $clsobj->setSsl($config['SSL_DIR'].$config['SSLCERT_PATH'],$config['SSL_DIR'].$config['SSLKEY_PATH']);
	        self::$cls[$cls]=$clsobj;
	    }
	    return self::$cls[$cls];
	}
	public function __construct($appid,$mchid,$key,$pubAcc='',$pubAccHint=''){
		$this->appid=$appid;
		$this->mchid=$mchid;
		$this->key=$key;
		$this->pubAcc=$pubAcc;
		$this->pubAccHint=$pubAccHint;
	}
	public function setSsl($sert,$key){
		$this->sslcert_path=$sert;
		$this->sslkey_path=$key;
		return $this;
	}
	public function setProxy($ip,$port){
		$this->proxy_ip=$ip;
		$this->proxy_port=$port;
		return $this;
	}
	public function get($key,$default=''){
		if (!isset($this->{$key}))return $default;
		return $this->{$key};
	}
}