<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Wechat;
use LSYS\PayGateway\Utils;
use LSYS\PayGateway\Exception;
use LSYS\PayGateway\Pay\PayParam;
use LSYS\PayGateway\Pay\PayRender;
use LSYS\PayGateway\Pay\PayAdapterCallback;
use LSYS\PayGateway\Pay\QueryParam;
class PayWap extends PayNotify implements PayAdapterCallback{
	public static $save_key="__lpay_param__";
	protected $_session;
	public function __construct(PayWapConfig $config,\LSYS\Session $session=null){
		$this->_config=$config;
		$this->_session=$session?$session:\LSYS\Session\DI::get()->session();
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
		require_once (__DIR__."/../../../../../libs/wechat/lib/WxPay.JsApiPay.php");
		\WxPayApi::$config=$this->_config->getWxPayConfigObj();
		$state=uniqid();
		$this->_session->set('__PAYGATEWAY_WECAHT_PAY__', array(
			'_param_'=>serialize($pay_param),
			'_state_'=>$state
		));
		$pay_url =$this->_config->getOauthReturnUrl();
		$tools = new \JsApiPay();
		$url = $tools->GetOpenidUrl(urlencode($pay_url),$state);
		return new PayRender(PayRender::OUT_URL, $url);
	}
	/**
	 * refund page,get pay param
	 * @return NULL|\LSYS\PayGateway\Pay\PayParam
	 */
	public static function getPayParam(\LSYS\Session $session=null){
	    $session=$session?$session:\LSYS\Session\DI::get()->session();
		$payparam=$session->get("__PAYGATEWAY_WECAHT_PAY__",[]);
		if (!isset($payparam['_param_'])) return null;
		/**
		 * @var PayParam $pay_param
		 */
		$pay_param=@unserialize($payparam['_param_']);
		if (!$pay_param instanceof PayParam) return null;
		//check other param
		if (!isset($_GET['state'])||!isset($_GET['code'])) return null;
		if (!isset($payparam['_state_'])) return null;
		if ($_GET['state']!=$payparam['_state_']) return null;
		return $pay_param;
	}
	
	/**
	 * 获取支付JS
	 * @param null $pay_param
	 * @throws Exception
	 * @return boolean|json数据，可直接填入js函数作为参数
	 */
	public function getPayJs(PayParam $pay_param){
		require_once (__DIR__."/../../../../../libs/wechat/lib/WxPay.JsApiPay.php");
		\WxPayApi::$config=$this->_config->getWxPayConfigObj();
		$pay_url =$this->_config->getReturnUrl();
		if (!isset($_GET['code'])) throw new Exception('oauth code is miss,plase try pay again');
		$tools = new \JsApiPay();
		try{
			$openid = @$tools->getOpenidFromMp($_GET['code']);
		}catch (\WxPayException $e){
			throw new Exception($e->getMessage(),$e->getCode(),$e);
		}
		$notify_url =$this->_config->getNotifyUrl();
		
		$body=$pay_param->getTitle();
		$attach=$pay_param->getBody();
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
// 		$input->SetGoods_tag("");
		$input->SetNotify_url($notify_url);
		$input->SetTrade_type("JSAPI");
		$input->SetOpenid($openid);
		try{
			$order = \WxPayApi::unifiedOrder($input);
			if (isset($order['return_code'])&&$order['return_code']=='FAIL'){
				throw new Exception(@$order['return_msg']);
			}
			if (isset($order['result_code'])&&$order['result_code']=='FAIL'){
				throw new Exception(@$order['return_msg']);
			}
			$jsApiParameters = $tools->GetJsApiParameters($order);
		}catch (\WxPayException $e){
			throw new Exception($e->getMessage(),$e->getCode(),$e);
		}
		return $jsApiParameters;
	}
	/**
	 * 获取支付HTML
	 * @param PayParam $pay_param
	 * @param array $jsApiParameters
	 * @param string $auto_pay
	 * @return string
	 */
	public function renderJs(PayParam $pay_param,$jsApiParameters,$auto_pay=true){
		$auto_pay=$auto_pay?1:0;
		$return_url=$this->_config->getReturnUrl();
		$op=strpos($return_url, "?")!==false?"&":"?";
		$sn=Utils::encodeUrl($pay_param->getSn(),$this->_config->getWxPayConfigObj()->APPSECRET);
		$return_url.=$op.self::$save_key."=".$sn;
		$cancel_url=$pay_param->getCancelUrl();
		ob_start();
		require_once (__DIR__."/../../../../../libs/wechat/utils/pay.php");
		$html=ob_get_contents();
		ob_end_clean();
		return $html;
	}
	public function payCallback(){
		//不能在渲染完删除SESSION,因为微信电脑版有次后台的页面请求..
		$this->_session->delete("__PAYGATEWAY_WECAHT_PAY__");
		if (!isset($_GET[self::$save_key]))  return (new \LSYS\PayGateway\Pay\PayResult\FailResult($_GET,'pay sn not find'))->setLocalFail();
		$sn=Utils::decodeUrl($_GET[self::$save_key],$this->_config->getWxPayConfigObj()->APPSECRET);
		if (empty($sn)) return (new \LSYS\PayGateway\Pay\PayResult\FailResult($_GET,'sign is fail'))->setSignFail();
		$param=new QueryParam($sn, null, null);
		return $this->query($param);
	}
	
}