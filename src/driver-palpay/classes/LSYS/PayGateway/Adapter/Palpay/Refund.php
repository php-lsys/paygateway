<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Palpay;
use LSYS\PayGateway\Pay\RefundParam;
use LSYS\PayGateway\Pay\RefundResult;
use PayPal\PayPalAPI\RefundTransactionRequestType;
use PayPal\Service\PayPalAPIInterfaceServiceService;
use PayPal\PayPalAPI\RefundTransactionReq;
use PayPal\CoreComponentTypes\BasicAmountType;
class Refund implements  \LSYS\PayGateway\Pay\RefundAdapter{
	/**
	 * @var Config
	 */
	protected $_config;
	public function __construct(Config $config){
		$this->_config=$config;
	}
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
	/**
	 * refund money
	 * @param RefundParam $refund_param
	 * @return RefundResult
	 */
	public function refund(RefundParam $refund_param){
		$msg=$refund_param->getRefundMsg();
		$recharge_pay_no= $refund_param->getPayNo();
		$refund_money = $refund_param->getRefundPayMoney($this->_config->getCurrencyCode());
		$total_money = $refund_param->getTotalPayMoney($this->_config->getCurrencyCode());
		$return_no = $refund_param->getReturnNo();
		$config=$this->_config();
		/*
		 * The RefundTransaction API operation issues a refund to the PayPal account holder associated with a transaction.
		 This sample code uses Merchant PHP SDK to make API call
		 */
		$refundReqest = new RefundTransactionRequestType();
		if ($refund_money>=$total_money){
			$refundReqest->RefundType ='Full';
		}else{
			$refundReqest->RefundType ='Partial';
			$refundReqest->Amount = new BasicAmountType($currencyCode, $refund_money);
		}
		/*
		 *  Either the `transaction ID` or the `payer ID` must be specified.
		 PayerID is unique encrypted merchant identification number
		 For setting `payerId`,
		 `refundTransactionRequest.setPayerID("A9BVYX8XCR9ZQ");`
		
		 Unique identifier of the transaction to be refunded.
		 */
		$refundReqest->TransactionID = $recharge_pay_no;
		/*
		 *  (Optional)Type of PayPal funding source (balance or eCheck) that can be used for auto refund. It is one of the following values:
		
		 any � The merchant does not have a preference. Use any available funding source.
		
		 default � Use the merchant's preferred funding source, as configured in the merchant's profile.
		
		 instant � Use the merchant's balance as the funding source.
		
		 eCheck � The merchant prefers using the eCheck funding source. If the merchant's PayPal balance can cover the refund amount, use the PayPal balance.
		
		 */
		$refundReqest->RefundSource = 'any';
		$refundReqest->Memo = $msg;
		$refundReqest->InvoiceID=$return_no;
		/*
		 *
		 (Optional) Maximum time until you must retry the refund.
		 */
		//$refundReqest->RetryUntil = $_REQUEST['retryUntil'];
		
		$refundReq = new RefundTransactionReq();
		$refundReq->RefundTransactionRequest = $refundReqest;
		/*
		 * 	 ## Creating service wrapper object
		 Creating service wrapper object to make API call and loading
		 Configuration::getAcctAndConfig() returns array that contains credential and config parameters
		 */
		$paypalService = new PayPalAPIInterfaceServiceService(\Configuration::getAcctAndConfig());
		try {
			/* wrap API method calls on the service object with a try catch */
			$refundResponse = $paypalService->RefundTransaction($refundReq);
		} catch (\Exception $ex) {
		    return (new \LSYS\PayGateway\Pay\RefundResult\FailResult($ex->getTraceAsString(),$ex->getMessage()))->setLocalFail();
		}
		
		if ($refundResponse->Ack!='Success'){
			$err=array_shift($refundResponse->Errors);
			return (new \LSYS\PayGateway\Pay\RefundResult\FailResult($refundResponse->Errors,$refund_no, $err->LongMessage))->setParam($refundResponse);
		}
		return (new \LSYS\PayGateway\Pay\RefundResult\IngResult(null,$return_no,$refundResponse->CorrelationID))->setParam($refundResponse);
	}
}