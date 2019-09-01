<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Qpay;

use LSYS\PayGateway\Pay\PayParam;
use LSYS\PayGateway\Pay\PayRender;
use LSYS\PayGateway\Pay\PayAdapterCallback;
use LSYS\PayGateway\Pay\QueryParam;
use LSYS\PayGateway\Utils;
class PayWap extends PayNotify implements PayAdapterCallback{
	public static $save_key="__sn__";
	public function __construct(PayWapConfig $config){
		$this->_config=$config;
	}
	public static function supportType($type){
	    return $type&self::TYPE_WECHAT;
	}
	/**
	 * next action : pay_js
	 * {@inheritDoc}
	 * @see \LSYS\PayGateway\Pay\PayAdapter::payRender()
	 */
	public function payRender(PayParam $pay_param){
		$body=$pay_param->getTitle();
		$attach='';
		$sn=$pay_param->getSn();
		$money=intval($pay_param->getPayMoney()*100);
		$timeout=$pay_param->getTimeout();
		$timeout||$timeout=time()+3600*24*7;
		$ctime=$pay_param->getCreateTime();
		
		$param=array();
		$param['body']=$body;
		$param['attach']=$attach;
		$param['out_trade_no']=$sn;
		$param['fee_type']='CNY';
		$param['total_fee']=$money;
		$param['spbill_create_ip']=Utils::clientIp();
		$param['time_start']=date("YmdHis",$ctime);
		$param['time_expire']=date("YmdHis",$timeout);
		$param['trade_type']='JSAPI';
		$param['notify_url']=$this->_config->getNotifyUrl();
		//$param['limit_pay']='no_balance';
		//$param['contract_code']='';
		//$param['promotion_tag']='';
		//$param['device_info']='';
		
		$xml=Tools::getToXml($param, $this->_config);		
		$url="https://qpay.qq.com/cgi-bin/pay/qpay_unified_order.cgi";
		$result=Tools::post($url, $xml, $this->_config);
		$result=Tools::parse($result, $this->_config);
		//vars
		$html=$this->_render($sn,$result['prepay_id']);
		return new PayRender(PayRender::OUT_HTML, $html);
	}
	//render pay html
	protected function _render($sn,$tokenId){
		$return_url=$this->_config->getReturnUrl();
		
		$op=strpos($return_url, "?")!==false?"&":"?";
		$sn=Utils::encodeUrl($sn,$this->_config->get("key"));
		$return_url.=$op.self::$save_key."=".$sn;
		
		$cancel_url=$pay_param->getCancelUrl();
		$pubAcc=$this->_config->get("pubAcc");
		$pubAccHint=$this->_config->get("pubAccHint");
		ob_start();
		require_once (__DIR__."/../../../../../libs/qpay/pay_page.php");
		$html=ob_get_contents();
		ob_end_clean();
		return $html;
	}
	public function payCallback(){
	    if (!isset($_GET[self::$save_key]))  return (new \LSYS\PayGateway\Pay\PayResult\FailResult($_GET,'pay sn not find'))->setLocalFail();
		$sn=Utils::decodeUrl($_GET[self::$save_key],$this->_config->get("key"));
		if (empty($sn)) return (new \LSYS\PayGateway\Pay\PayResult\FailResult($_GET,'sign is fail'))->setSignFail();
		$param=new QueryParam($sn, null, null);
		return $this->query($param);
	}
}