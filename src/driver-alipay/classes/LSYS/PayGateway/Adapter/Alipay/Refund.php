<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Alipay;
use LSYS\PayGateway\Pay\RefundNotify;
use LSYS\PayGateway\Pay\RefundResult;
use LSYS\PayGateway\Pay\RefundParam;
class Refund implements  \LSYS\PayGateway\Pay\RefundAdapter, RefundNotify{
	/**
	 * @var RefundConfig
	 */
	protected $_config;
	public function __construct(RefundConfig $config){
		$this->_config=$config;
		$this->_config->setMd5();
	}
	/**
	 * refund money
	 * @param RefundParam $refund_param
	 * @return RefundResult
	 */
	public function refund(RefundParam $refund_param){
		$alipay_config=$this->_config->asArray();
		/**************************请求参数**************************/
		$notify_url=$this->_config->getNotifyUrl();
		
		
		$recharge_pay_no= $refund_param->getPayNo();
		$refund_money= $refund_param->getRefundPayMoney();
		
		$msg=$refund_param->getRefundMsg();
		if (empty($msg))$msg='退款';
		$msg=str_replace(array("^",'|','&','$','#',"\n","\r","\t"," ",'%','`','(',')','-','+','\\','*'), "", $msg);
		//退款批次号
		$batch_no = date("Ymd").$refund_param->getReturnNo();
		//必填，每进行一次即时到账批量退款，都需要提供一个批次号，必须保证唯一性
		
		//退款请求时间
		$refund_date = date("Y-m-d H:i:s");
		//必填，格式为：yyyy-MM-dd hh:mm:ss
		
		//退款总笔数
		$batch_num = 1;
		//必填，即参数detail_data的值中，“#”字符出现的数量加1，最大支持1000笔（即“#”字符出现的最大数量999个）
		
		//单笔数据集
		$detail_data ="{$recharge_pay_no}^{$refund_money}^{$msg}";
		//必填，格式详见“4.3 单笔数据集参数说明”
		
		
		/************************************************************/
		
		//构造要请求的参数数组，无需改动
		$parameter = array(
				"service" => "refund_fastpay_by_platform_nopwd",
				"partner" => trim($alipay_config['partner']),
				"notify_url"	=> $notify_url,
				"batch_no"	=> $batch_no,
				"refund_date"	=> $refund_date,
				"batch_num"	=> $batch_num,
				"detail_data"	=> $detail_data,
				"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
		);
// 		print_r($parameter);
// 		exit;
		require_once (__DIR__."/../../../../../libs/alipay_refund_nopwd/lib/alipay_submit.class.php");
		//建立请求
		$alipaySubmit = new \AlipaySubmit($alipay_config);
		$html_text = $alipaySubmit->buildRequestHttp($parameter);
		//解析XML
		//注意：该功能PHP5环境及以上支持，需开通curl、SSL等PHP配置环境。建议本地调试时使用PHP开发软件
		$doc = new \DOMDocument();
		@$doc->loadXML($html_text);
		
		//请在这里加上商户的业务逻辑程序代码
		
		//——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
		
		//获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表
		
		//解析XML
		if(@$doc->getElementsByTagName( "is_success" )->length){
			if($doc->getElementsByTagName( "is_success" )->item(0)->nodeValue!='F'){
			    return (new \LSYS\PayGateway\Pay\RefundResult\IngResult($html_text,$refund_param->getReturnNo(),$batch_no))->setParam($doc);
			}else{
			    return (new \LSYS\PayGateway\Pay\RefundResult\FailResult($html_text,$doc->getElementsByTagName( "error" )->item(0)->nodeValue,$refund_param->getReturnNo()))->setParam($doc)->setLocalRollback();
			}
		}
		if(@$doc->getElementsByTagName( "error" )->item(0)->nodeValue){
		    $err=strval($doc->getElementsByTagName( "error" )->item(0)->nodeValue);
		}else $err='xml parse fail';
		return (new \LSYS\PayGateway\Pay\RefundResult\FailResult($html_text,$err,$refund_param->getReturnNo()))->setParam($doc);
	}
	
	public function refundNotify(){
		ignore_user_abort(true);
		$alipay_config=$this->_config->asArray();
		require_once (__DIR__."/../../../../../libs/alipay_refund_nopwd/lib/alipay_notify.class.php");
		$alipayNotify = new \AlipayNotify($alipay_config);
		if(!isset($_POST["sign"])||!$alipayNotify->verifyNotify()){
		    return (new \LSYS\PayGateway\Pay\RefundResult\FailResult($_POST,'sign is fail'))->setSignFail()->setParam($_POST);
		}
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//请在这里加上商户的业务逻辑程序代
		//Loger::instance(Loger::TYPE_REFUND)->add($_POST);
		//——请根据您的业务逻辑来编写程序（以下代码仅作参考）——

		//获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表

		//退款批次号
		$refund_no = substr(@$_POST['batch_no'],8);
		$batch_no = @$_POST['batch_no'];
		//必填


		//退款成功总数
		$success_num = @$_POST['success_num'];
		//必填，0<= success_num<= batch_num


		//处理结果详情
		$result_details = @$_POST['result_details'];
		//必填，详见“6.3 处理结果详情说明”
		// 		201003120625277
		// 		9^10.00^NOT_THI
		// 		S_PARTNERS_T
		// 		RAD
		// 		交易退款数据集$收费退款数据集|分润退款数据集|分润退款数据集|...|分润
		// 		退款数据集$$退子交易
		//解冻结果明细
		$unfreezed_deta = @$_POST['unfreezed_deta'];
		//格式：解冻结订单号^冻结订单号^解冻结金额^交易号^处理时间^状态^描述码

		if($success_num>0){//退款成功
		    $result=(new \LSYS\PayGateway\Pay\RefundResult\SuccResult($_POST,$refund_no,$batch_no))->setParam($_POST);
		}else{
		    $result=(new \LSYS\PayGateway\Pay\RefundResult\FailResult($_POST,$result_details,$refund_no))->setParam($_POST)->setLocalRollback();
		}
		
		return $result;
		//请不要修改或删除
		//判断是否在商户网站中已经做过了这次通知返回的处理
		//如果没有做过处理，那么执行商户的业务程序
		//如果有做过处理，那么不执行商户的业务程序
		//调试用，写文本函数记录程序运行情况是否正常
		//logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
		//——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	}
	public function refundNotifyOutput($status=true,$msg=null){
		if ($status){
			http_response_code(200);
			echo "success";
			die();
		}else{
			echo "fail";
			die();
		}
	}
	
}