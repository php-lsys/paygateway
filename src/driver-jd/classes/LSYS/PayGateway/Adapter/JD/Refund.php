<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\JD;
use LSYS\PayGateway\Pay\RefundNotify;
use LSYS\PayGateway\Pay\RefundParam;
use LSYS\PayGateway\Pay\RefundResult;
use com\jdjr\pay\demo\common\ConfigUtil;
use com\jdjr\pay\demo\common\HttpUtils;
use com\jdjr\pay\demo\common\XMLUtil;
class Refund implements  \LSYS\PayGateway\Pay\RefundAdapter, RefundNotify{
	/**
	 * @var RefundConfig
	 */
	protected $_config;
	public function __construct(RefundConfig $config){
		$this->_config=$config;
	}
	/**
	 * refund money
	 * @param RefundParam $refund_param
	 * @return RefundResult
	 */
	public function refund(RefundParam $refund_param){
		
		$notify_url=$this->_config->getNotifyUrl();
		$msg=$refund_param->getRefundMsg();
		$recharge_pay_sn= $refund_param->getPaySn();
		$refund_money = strval(intval($refund_param->getRefundPayMoney()*100));
		$total_money = strval(intval($refund_param->getTotalPayMoney()*100));
		$return_no = $refund_param->getReturnNo();
		$private=$this->_config->getPrivateKeyPath();
		$public=$this->_config->getPublicKeyPath();
	
		
		require_once (__DIR__.'/../../../../../libs/jdPay2Demo/utils/ConfigUtil.php');
		require_once (__DIR__.'/../../../../../libs/jdPay2Demo/com/jdjr/pay/demo/common/HttpUtils.php');
		require_once (__DIR__.'/../../../../../libs/jdPay2Demo/com/jdjr/pay/demo/common/XMLUtil.php');
		
		
		$merchant=$this->_config->getMerchant();
		$deskey=$this->_config->getDeskey();
		$notify_url=$this->_config->getNotifyUrl();
		ConfigUtil::$config=array(
			'merchantNum' => $merchant,
			'desKey' => $deskey,
			'notifyUrl' =>$notify_url,
		);
		
		
		$param=[];
		$param["version"]='V2.0';
		$param["merchant"]=$merchant;
		$param["currency"]= 'CNY';
		$param["tradeNum"]=$return_no;
		$param["oTradeNum"]=$recharge_pay_sn;
		$param["amount"]=$refund_money;
		$param["tradeTime"]=date("YmdHis");
		$param["notifyUrl"]=$notify_url;
		$param["note"]=$msg;
		
		// 		$param["version"]="V2.0";
		// 		$param["merchant"]="22294531";
		// 		$param["tradeNum"]="20160621121848";
		// 		$param["oTradeNum"]="1466415142002";
		// 		$param["amount"]="1";
		// 		$param["tradeTime"]="20160621122924";
		// 		$param["notifyUrl"]="http://localhost/jdPay2Demo/com/jdjr/pay/demo/action/AsnyNotify.php";
		// 		$param["note"]="";
		// 		$param["currency"]="CNY";
		
		$reqXmlStr = XMLUtil::encryptReqXml($param,$private);
		$url = ConfigUtil::get_val_by_key("refundUrl");
		//echo "请求地址：".$url;
		//echo "----------------------------------------------------------------------------------------------";
		$httputil = new HttpUtils();
		@list ( $return_code, $return_content )  = $httputil->http_post_data($url, $reqXmlStr);
		//echo $return_content."\n";
		$resData;
		$flag=XMLUtil::decryptResXml($return_content,$resData,$public);
		//echo var_dump($resData);
		
		if(!$flag){
		    return (new \LSYS\PayGateway\Pay\RefundResult\FailResult($return_content,'sign is fail'))->setSignFail();
		}
		$status = $resData['status'];
		if($status=="0"){
		    return (new \LSYS\PayGateway\Pay\RefundResult\IngResult($return_content,$return_no))->setParam($resData);
		}elseif($status=="1"){
		    return (new \LSYS\PayGateway\Pay\RefundResult\SuccResult($return_content,$return_no,'Time:'.$resData['tradeTime']))->setParam($resData);
		}elseif ($status=="2"){
		    return (new \LSYS\PayGateway\Pay\RefundResult\FailResult($return_content,strip_tags($return_content),$refund_no))->setParam($resData)->setLocalRollback();
		}
	}
	
	public function refundNotify(){
		ignore_user_abort(true);
		
		require_once (__DIR__.'/../../../../../libs/jdPay2Demo/utils/ConfigUtil.php');
		require_once (__DIR__.'/../../../../../libs/jdPay2Demo/com/jdjr/pay/demo/common/XMLUtil.php');
		
		$merchant=$this->_config->getMerchant();
		$notify_url=$this->_config->getNotifyUrl();
		
		$private=$this->_config->getPrivateKeyPath();
		$public=$this->_config->getPublicKeyPath();
		$deskey=$this->_config->getDeskey();
		
		ConfigUtil::$config=array(
			'merchantNum' => $merchant,
			'desKey' => $deskey,
			'notifyUrl' =>$notify_url,
		);
		
		$xml=file_get_contents("php://input");
		if(empty($xml)){
		    return  (new \LSYS\PayGateway\Pay\RefundResult\FailResult($xml,'sign is fail'))->setSignFail();
		}
		$resdata;
		$falg = XMLUtil::decryptResXml($xml, $resdata,$public);
		if(!falg){
		    return  (new \LSYS\PayGateway\Pay\RefundResult\FailResult($xml,'sign is fail'))->setSignFail();
		}
		//Loger::instance(Loger::TYPE_REFUND)->add($this->supportName(),$xml);
		
		$batch_no=$resdata['tradeNum'];
		$status = $resData['status'];
		if($status=="0"){
		    $result=(new \LSYS\PayGateway\Pay\RefundResult\IngResult($xml,$batch_no))->setParam($resData);
		}elseif($status=="1"){
		    $result= (new \LSYS\PayGateway\Pay\RefundResult\SuccResult($xml,$return_no, 'Time:'.$resData['tradeTime']))->setParam($resData);
		}elseif ($status=="2"){
		    $result=(new \LSYS\PayGateway\Pay\RefundResult\FailResult($xml,strip_tags($xml),$refund_no))->setParam($resData)->setLocalRollback();
		}
		return   $result;
	}
	
	
	public function refundNotifyOutput($status=true,$msg=null){
		if ($status){
			echo "验签成功";
			die();
		}else{
			http_response_code(500);
			die($msg);
		}
	}
	
}