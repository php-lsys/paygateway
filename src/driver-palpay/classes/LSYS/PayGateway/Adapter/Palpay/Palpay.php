<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Palpay;
use PayPal\PayPalAPI\GetTransactionDetailsRequestType;
use LSYS\PayGateway\Pay\QueryParam;
use PayPal\PayPalAPI\GetTransactionDetailsReq;
use PayPal\Service\PayPalAPIInterfaceServiceService;
use LSYS\PayGateway\Pay\Query;
abstract class Palpay implements \LSYS\PayGateway\Pay\PayAdapterSimple, Query{
	protected $_config;
	protected function _config(){
		$mode=$this->_config->getMode();//"sandbox",'live'
		$username=$this->_config->getUsername();//"362724880-facilitator_api1.qq.com"
		$password=$this->_config->getPassword();//"RDYZD6FAEK28865K"
		$sign=$this->_config->getSignature();//"AFcWxV21C7fd0v3bYYYRCpSSRl31AFOI1naadHvh1c1vzVqRCY9c2mFZ"
		$config = array(
				"mode" => $mode,
				'log.LogEnabled' => false,
				'log.FileName' => sys_get_temp_dir().'/PayPal.log',
				'log.LogLevel' => 'FINE',
				"acct1.UserName" => $username,
				"acct1.Password" => $password,
				"acct1.Signature" => $sign,
		);
		return $config;
	}
	public function query(QueryParam $param){
		$config=$this->_config();
		$tid=$param->getPayNo();
		$sn=$param->getPaySn();
		if (empty($tid)) return (new \LSYS\PayGateway\Pay\PayResult\FailResult(null,'this order unkown pay status'))->setLocalFail();
		/*
		 * The GetTransactionDetails API operation obtains information about a specific transaction.
		 */
		
		$transactionDetails = new GetTransactionDetailsRequestType();
		/*
		 * Unique identifier of a transaction.
		 */
		$transactionDetails->TransactionID = $tid;
		$request = new GetTransactionDetailsReq();
		$request->GetTransactionDetailsRequest = $transactionDetails;
		/*
		 * 	 ## Creating service wrapper object
		 Creating service wrapper object to make API call and loading
		 Configuration::getAcctAndConfig() returns array that contains credential and config parameters
		 */
		$paypalService = new PayPalAPIInterfaceServiceService($config);
		try {
			/* wrap API method calls on the service object with a try catch */
			$transDetailsResponse = $paypalService->GetTransactionDetails($request);
		} catch (\Exception $ex) {
		    return (new \LSYS\PayGateway\Pay\PayResult\FailResult($ex->getTraceAsString(),$ex->getMessage()))->setLocalFail();
		}
		if ($transDetailsResponse->Ack!='Success'){
		    if (is_array($transDetailsResponse->Errors))$err=array_shift($transDetailsResponse->Errors);
		    else $err=$transDetailsResponse->Errors;
		    return (new \LSYS\PayGateway\Pay\PayResult\FailResult(@$transDetailsResponse->Errors,$err->LongMessage,$refund_no));
		}
		return (new \LSYS\PayGateway\Pay\PayResult\SuccResult(null,$sn, $tid))->setParam($transDetailsResponse);
	}
}
