<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Alipay;
use LSYS\PayGateway\Pay\PayAdapterCallback;
use LSYS\PayGateway\Exception;
use LSYS\PayGateway\Pay\PayParam;
use LSYS\PayGateway\Pay\PayRender;
class PayWap extends Alipay implements PayAdapterCallback{
	public function __construct(PayConfig $config){
		parent::__construct($config);
		$this->_config->set0001();
	}
	public static function supportType($type){
	    return ($type&self::TYPE_WAP)&&(!($type&self::TYPE_WECHAT));
	}
	/**
	 * {@inheritDoc}

	 */
	public function payRender(PayParam $pay_param){
		$alipay_config=$this->_config->asArray();
		$notify_url=$this->_config->getNotifyUrl();
		$return_url=$this->_config->getReturnUrl();
		//卖家支付宝帐户
		$seller_email = $this->_config->getSellerId();
	
		$out_trade_no=$pay_param->getSn();
		$total_fee=$pay_param->getPayMoney();
		$subject=$pay_param->getTitle();
		
		$end_url=$pay_param->getCancelUrl();
	
		//返回格式
		$format = "xml";
		//必填，不需要修改
	
		//返回格式
		$v = "2.0";
		//必填，不需要修改
	
		//请求号
		$req_id = date('Ymdhis');
		//必填，须保证每次请求都是唯一
	
		//**req_data详细信息**
	
	
		//订单名称
		//必填
	
		//请求业务参数详细
		$req_data = '<direct_trade_create_req><notify_url>' . $notify_url
		. '</notify_url><call_back_url>' . $return_url
		. '</call_back_url><seller_account_name>' . $seller_email
		. '</seller_account_name><out_trade_no>' . $out_trade_no
		. '</out_trade_no><subject>' . $subject 
		. '</subject><total_fee>'. $total_fee 
		. '</total_fee><merchant_url>'. $end_url 
		. '</merchant_url></direct_trade_create_req>';
	
		//必填
		/************************************************************/
		//构造要请求的参数数组，无需改动
		$para_token = array(
				"service" => "alipay.wap.trade.create.direct",
				"partner" => trim($alipay_config['partner']),
				"sec_id" => trim($alipay_config['sign_type']),
				"format"	=> $format,
				"v"	=> $v,
				"req_id"	=> $req_id,
				"req_data"	=> $req_data,
				"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
		);
		
		require_once (__DIR__."/../../../../../libs/alipay_wap/lib/alipay_submit.class.php");
		
		//建立请求
		$alipaySubmit = new \AlipaySubmit($alipay_config);
		
		$html_text = $alipaySubmit->buildRequestHttp($para_token);

		//URLDECODE返回的信息
		$html_text = urldecode($html_text);

		//解析远程模拟提交后返回的信息
		$para_html_text = $alipaySubmit->parseResponse($html_text);
		if(!isset($para_html_text['res_data'])){
		    $code=0;
			if (is_string($para_html_text)){
				parse_str($para_html_text, $output);
				if(isset($output['res_error'])){
				    $html_text=$output['res_error'];
				    $arr=@json_decode(@json_encode(new \SimpleXMLElement($html_text)),true);
				    if (isset($arr['code'])&&isset($arr['detail'])&&isset($arr['msg'])){
				        $code=$arr['code'];
				        $html_text=$arr['msg'].":".$arr['detail'];
				    }
				}
			}
			throw new Exception($html_text,$code);
		}
		//获取request_token
		$request_token = @$para_html_text['request_token'];
		/**************************根据授权码token调用交易接口alipay.wap.auth.authAndExecute**************************/
		//业务详细
		$req_data = '<auth_and_execute_req><request_token>' . $request_token . '</request_token></auth_and_execute_req>';
		//必填

		//构造要请求的参数数组，无需改动
		$parameter = array(
				"service" => "alipay.wap.auth.authAndExecute",
				"partner" => trim($alipay_config['partner']),
				"sec_id" => trim($alipay_config['sign_type']),
				"format"	=> $format,
				"v"	=> $v,
				"req_id"	=> $req_id,
				"req_data"	=> $req_data,
				"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
		);
		//建立请求
		$alipaySubmit = new \AlipaySubmit($alipay_config);
		
		$html_text = $alipaySubmit->buildRequestForm($parameter, 'get', '');
		
		return new PayRender(PayRender::OUT_HTML, $html_text);
	}
	
	protected function _verify(){
		$alipay_config=$this->_config->asArray();
		require_once (__DIR__."/../../../../../libs/alipay_wap/lib/alipay_notify.class.php");
		return new \AlipayNotify($alipay_config);
	}
	
	
	public function payCallback(){
		$alipayNotify=$this->_verify();
		if(!isset($_GET["sign"])||!$alipayNotify->verifyReturn()){
			return (new \LSYS\PayGateway\Pay\PayResult\FailResult(null,'sign is fail'))->setSignFail();
		}
		//Loger::instance(Loger::TYPE_PAY_CALLBACK)->add($this->supportName(),$_GET);
		$out_trade_no=isset($_GET['out_trade_no'])?$_GET['out_trade_no']:null;
		$trade_no=isset($_GET['trade_no'])?$_GET['trade_no']:null;
		$result=isset($_GET['result'])?$_GET['result']:null;
		switch ($result){
			case 'success':
			    $_result=(new \LSYS\PayGateway\Pay\PayResult\SuccResult($_GET,$out_trade_no,$trade_no))->setParam($_GET);
				break;
			default:
			    $_result=(new \LSYS\PayGateway\Pay\PayResult\FailResult($_GET,$result,$out_trade_no,$trade_no))->setParam($_GET);
				break;
		}
		return $_result;
	}
	public function payNotify(){
		ignore_user_abort(true);
		$alipayNotify=$this->_verify();
		if(!isset($_POST["sign"])||!$alipayNotify->verifyNotify()){
		    return (new \LSYS\PayGateway\Pay\PayResult\FailResult($_POST['notify_data'],'sign is fail'))->setSignFail();
		}
		$doc = new \DOMDocument();
		$xml=$alipayNotify->decrypt($_POST['notify_data']);
		if(!isset($xml)){
		    return (new \LSYS\PayGateway\Pay\PayResult\FailResult($_POST['notify_data'],"parse xml fail"))->setLocalFail();
		}
		@$doc->loadXML($xml);
		if(@empty($doc->getElementsByTagName( "notify" )->item(0)->nodeValue) ){
		    return (new \LSYS\PayGateway\Pay\PayResult\FailResult($xml,"xml struct wrong"))->setLocalFail();
		}
		//商户订单号
		$out_trade_no = $doc->getElementsByTagName( "out_trade_no" )->item(0)->nodeValue;
		//支付宝交易号
		$trade_no = $doc->getElementsByTagName( "trade_no" )->item(0)->nodeValue;
		$buyer_email = $doc->getElementsByTagName( "buyer_email" )->item(0)->nodeValue;
		$total_fee = $doc->getElementsByTagName( "total_fee" )->item(0)->nodeValue;
		//交易状态
		$trade_status = $doc->getElementsByTagName( "trade_status" )->item(0)->nodeValue;
		//商品购买
		if($trade_status == 'TRADE_FINISHED'||$trade_status == 'TRADE_SUCCESS') {
		    $result=new \LSYS\PayGateway\Pay\PayResult\SuccResult($xml,$out_trade_no,$trade_no,$doc);
		}else $result=new \LSYS\PayGateway\Pay\PayResult\FailResult($xml,$trade_status,$out_trade_no,$trade_no);
		$result->setMoney($total_fee)->setPayAccount($buyer_email);
		return  $result;
	}
}