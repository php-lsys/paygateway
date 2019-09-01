<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Wechat;
use LSYS\PayGateway\Exception;
use LSYS\PayGateway\Pay\PayParam;
use LSYS\PayGateway\Pay\PayRender;
use LSYS\PayGateway\Utils;
use LSYS\PayGateway\Pay\QueryParam;
use LSYS\PayGateway\Pay\PayAdapterCallback;
class PayCode extends PayNotify implements PayAdapterCallback{
	public static $save_key='__sn__';
	/**
	 * @var PayCodeConfig
	 */
	protected $_config;
	public function __construct(PayCodeConfig $config){
		$this->_config=$config;
	}
	public static function supportType($type){
	    return $type&self::TYPE_PC;
	}
	public function payRender(PayParam $pay_param){
		
		require_once (__DIR__."/../../../../../libs/wechat/lib/WxPay.Api.php");
		\WxPayApi::$config=$this->_config->getWxPayConfigObj();
		
		$notify_url= $this->_config->getNotifyUrl();
		$qrcode_url= $this->_config->getQrcodeUrl();
		$return_url= $this->_config->getReturnUrl();
		$check_url= $this->_config->getCheckUrl();
		
		
		$op=strpos($return_url, "?")!==false?"&":"?";
		$sn=Utils::encodeUrl($pay_param->getSn(),$this->_config->getWxPayConfigObj()->APPSECRET);
		$return_url.=$op.self::$save_key."=".$sn;
		
		$body=$pay_param->getBody();
		$attach='';
		$sn=$pay_param->getSn();
		$money=intval($pay_param->getPayMoney()*100);
		$timeout=$pay_param->getTimeout();
		$timeout||$timeout=time()+3600*24*7;
		$pids=implode(",",$pay_param->getGoods());
		if(empty($pids))$pids='0';
		$ctime=$pay_param->getCreateTime();
		
		
		$input = new \WxPayUnifiedOrder();
		$input->SetBody($body);
		$input->SetAttach($attach);
		$input->SetOut_trade_no($sn);
		$input->SetTotal_fee($money);
		$input->SetTime_start(date("YmdHis",$ctime));
		$input->SetTime_expire(date("YmdHis",$timeout));
		$input->SetProduct_id($pids);
		// 		$input->SetGoods_tag("test");
		$input->SetNotify_url($notify_url);
		$input->SetTrade_type("NATIVE");
		
		try{
			$result = \WxPayApi::unifiedOrder($input);
		}catch (\WxPayException $e){
			throw new Exception($e->getMessage(),$e->getCode(),$e);
		}
		if (!isset($result["code_url"])){
			throw new Exception('wrong wechat return:'.$result['return_msg']);
		}
		$url = $result["code_url"];
		return new PayRender(PayRender::OUT_QRCODE, array(
			'code_url'=>$url,
			'sn'=>$sn,
			'qrcode_url'=>$qrcode_url,
			'return_url'=>$return_url,
			'check_url'=>$check_url,
		));
	}
	public function payCallback(){
	    if (!isset($_GET[self::$save_key]))  return (new \LSYS\PayGateway\Pay\PayResult\FailResult($_GET,'pay sn not find'))->setLocalFail();
		$sn=Utils::decodeUrl($_GET[self::$save_key],$this->_config->getWxPayConfigObj()->APPSECRET);
		if (empty($sn)) return (new \LSYS\PayGateway\Pay\PayResult\FailResult($_GET,'sign is fail'))->setSignFail();
		$param=new QueryParam($sn, null, null);
		return $this->query($param);
	}
}