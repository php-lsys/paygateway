<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Pay;
use LSYS\PayGateway\Utils;
use Endroid\QrCode\QrCode;
class PayRender{
	public static $key='lpay_key...';
	const OUT_HTML=0;
	const OUT_URL=1;
	const OUT_QRCODE=2;
	const OUT_CREDITCARD=3;
	const OUT_VARS=4;
	protected $_out;
	protected $_data;
	
	public function __construct($out,$data){
		$this->_out=$out;
		$this->_data=$data;
	}
	public function getOut(){
		return $this->_out;
	}
	public function getData(){
		return $this->_data;
	}
	public function __toString(){
		try{
			switch ($this->_out){
				case PayRender::OUT_HTML: return $this->_data;
				case PayRender::OUT_URL: return $this->_renderUrl();
				case PayRender::OUT_QRCODE: return $this->_renderQrcode();
				case PayRender::OUT_CREDITCARD: return $this->_creditCard();
				case PayRender::OUT_VARS: return $this->_json();
			}
		}catch (\Exception $e){
			return $e->getTraceAsString();
		}
		return '';
	}
	
	//链接
	protected function _renderUrl(){
		if (!headers_sent()) Utils::redirectUrl($this->_data);
	}
	
	//二维码
	
	protected static function _qrcodeKey($string){
		return md5(self::$key.$string);
	}
	protected function _renderQrcode(){
		extract($this->_data);
		//$qrcode_url
		//$return_url
		//$check_url
		//$code_url
		//$sn
		$key=self::_qrcodeKey($code_url);
		$op=strpos($qrcode_url, "?")!==false?"&":"?";
		$qrcode_url=$qrcode_url.$op."code_url=".urlencode($code_url)."&key=".$key;
		
		$key=self::_qrcodeKey($sn);
		$op=strpos($check_url, "?")!==false?"&":"?";
		$check_url=$check_url.$op."sn=".$sn."&key=".$key;
		
		ob_start();
		//$qrcode_url
		//$return_url
		//$check_url
		require_once (__DIR__."/../../../../libs/lpay_utils/qrcode.php");
		$html=ob_get_contents();
		ob_end_clean();
		return $html;
	}
	
	//-----二维码辅助函数
	/**
	 * 显示二维码错误
	 */
	protected static function _showBadQucode(){
		@Header("Content-type: image/png");
		readfile(__DIR__."/../../../../libs/lpay_utils/error.png");
		die();
	}
	/**
	 * 显示付款二维码
	 * @param string $label
	 * @param array $color
	 */
	public static function qrcodeRender($font_path='',$label='',$logo='',$color=array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0)){
		if (!isset($_GET['code_url'])||!isset($_GET['key']))self::_showBadQucode();
		$url = urldecode($_GET['code_url']);
		$skey=self::_qrcodeKey($url);
		if($skey!=$_GET['key'])self::_showBadQucode();
		$qrCode = new QrCode();
		$qrCode
			->setText($url)
			->setSize(300)
			->setPadding(10)
			->setErrorCorrection('high')
			->setForegroundColor($color)
			->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
			->setLabel($label)
			->setLabelFontPath($font_path)
			->setLabelFontSize(16)
			->setImageType(QrCode::IMAGE_TYPE_PNG)
			;
		if (is_file($logo))$qrCode->setLogo($logo);
		// now we can directly output the qrcode
		header('Content-Type: '.$qrCode->getContentType());
		$qrCode->render();
	}
	/**
	 * 获取订单号
	 * @return boolean|string
	 */
	public static function qrcodeGetSn(){
		if (!isset($_GET['sn'])||!isset($_GET['key']))return false;
		$sn = $_GET['sn'];
		$skey=self::_qrcodeKey($sn);
		if($skey!=$_GET['key'])return false;
		return $sn;
	}
	/**
	 * 输出付款状态
	 * @param string $status
	 */
	public static function qrcodeOutput($status=true,$data='',$name='callback'){
		if (!isset($_GET[$name]))$callback='callback';
		else $callback=strip_tags($_GET[$name]);
		$json_str=json_encode(array("status"=>boolval($status),'data'=>$data));
		echo $callback.'('.$json_str.')';
		die();
	}

	//信用卡
	protected function _creditCard(){
		extract($this->_data);
		ob_start();
		//$key
		//$pay_param
		//$pay_url
		require_once (__DIR__."/../../../../libs/lpay_utils/credit_card.php");
		$html=ob_get_contents();
		ob_end_clean();
		return $html;
	}
	
	/**
	 * 输出付款状态
	 * @param string $status
	 */
	public static function creditCardOutput($status=true,$data=''){
		echo json_encode(array("status"=>boolval($status),'data'=>$data));
		die();
	}
	
	//变量 APP等使用
	protected function _json(){
		return json_encode($this->_data);
	}
}