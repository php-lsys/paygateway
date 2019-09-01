<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Alipay;
use LSYS\PayGateway\Pay\Query;
use LSYS\PayGateway\Pay\QueryParam;
use LSYS\PayGateway\Pay\PayAdapterNotify;
abstract class Alipay implements \LSYS\PayGateway\Pay\PayAdapterSimple,Query,PayAdapterNotify{
    /**
     * @var PayConfig
     */
    protected $_config;
    public function __construct(PayConfig $config){
        $this->_config=$config;
    }
	public function query(QueryParam $param){
		$this->_config->setMd5();
		$alipay_config=$this->_config->asArray();
		require_once (__DIR__."/../../../../../libs/alipay_trade_query/lib/alipay_submit.class.php");
		/**************************请求参数**************************/
		//支付宝交易号
		$trade_no =$param->getPayNo();
		//支付宝交易号与商户网站订单号不能同时为空
		//商户订单号
		$out_trade_no =$param->getPaySn();
		/************************************************************/
		//构造要请求的参数数组，无需改动
		$parameter = array(
				"service" => "single_trade_query",
				"partner" => trim($alipay_config['partner']),
				"trade_no"	=> $trade_no,
				"out_trade_no"	=> $out_trade_no,
				"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
		);
		//建立请求
		$alipaySubmit = new \AlipaySubmit($alipay_config);
		$html_text = $alipaySubmit->buildRequestHttp($parameter);
		//解析XML
		//注意：该功能PHP5环境及以上支持，需开通curl、SSL等PHP配置环境。建议本地调试时使用PHP开发软件
		$xml = simplexml_load_string($html_text);
		$data = json_decode(json_encode($xml),TRUE);
		if (isset($data['is_success'])&&$data['is_success']=='T'){
			if (isset($data['response']['trade']['trade_status'])){
				if ($data['response']['trade']['trade_status']=='TRADE_SUCCESS'
					||$data['response']['trade']['trade_status']=='TRADE_FINISHED'){
					$out_trade_no=isset($data['response']['trade']['out_trade_no'])?$data['response']['trade']['out_trade_no']:$out_trade_no;
					$trade_no=isset($data['response']['trade']['trade_no'])?$data['response']['trade']['trade_no']:$trade_no;
					$succ=(new \LSYS\PayGateway\Pay\PayResult\SuccResult($html_text,$out_trade_no, $trade_no))->setParam($data);
					isset($data['response']['trade']['total_fee'])&&$succ->setMoney($data['response']['trade']['total_fee']);
					isset($data['response']['trade']['buyer_email'])&&$succ->setPayAccount($data['response']['trade']['buyer_email']);
					return $succ;
				}
			}
			return (new \LSYS\PayGateway\Pay\PayResult\IngResult($html_text,$out_trade_no, $trade_no))->setParam($data);
		}else if (isset($data['error'])&&$data['error']=='ILLEGAL_PARTNER_EXTERFACE'){
			//未签约
		    return (new \LSYS\PayGateway\Pay\PayResult\IngResult($html_text,$out_trade_no,$trade_no))->setParam($data);
		}else{
		    return (new \LSYS\PayGateway\Pay\PayResult\FailResult($html_text,@$data['error'],$out_trade_no,$trade_no))->setParam($data);
		}
	}
	public function payNotifyOutput($status=true,$msg=null){
		if ($status){
			http_response_code(200);
			echo "success";
			die();
		}else{
			$msg&&$msg=":".$msg;
			echo "fail".$msg;
			die();
		}
	}
}