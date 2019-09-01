<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Palpay;
use LSYS\PayGateway\Pay\PayAdapterCallback;
use LSYS\PayGateway\Pay\PayParam;
use LSYS\PayGateway\Pay\PayRender;
use LSYS\PayGateway\Exception;
use PayPal\EBLBaseComponents\PaymentDetailsItemType;
use PayPal\CoreComponentTypes\BasicAmountType;
use PayPal\EBLBaseComponents\SetExpressCheckoutRequestDetailsType;
use PayPal\PayPalAPI\SetExpressCheckoutReq;
use PayPal\PayPalAPI\SetExpressCheckoutRequestType;
use PayPal\Service\PayPalAPIInterfaceServiceService;
use PayPal\EBLBaseComponents\PaymentDetailsType;
use PayPal\PayPalAPI\GetExpressCheckoutDetailsRequestType;
use PayPal\PayPalAPI\GetExpressCheckoutDetailsReq;
use PayPal\EBLBaseComponents\DoExpressCheckoutPaymentRequestDetailsType;
use PayPal\PayPalAPI\DoExpressCheckoutPaymentRequestType;
use PayPal\PayPalAPI\DoExpressCheckoutPaymentReq;

use LSYS\PayGateway\Pay\Money;
class Pay extends Palpay implements PayAdapterCallback{
	public function __construct(PayConfig $config){
		$this->_config=$config;
	}
	public static function supportType($type){
	    return $type&(self::TYPE_WAP|self::TYPE_PC|self::TYPE_WECHAT);
	}
	public function payRender(PayParam $pay_param){
		$config=$this->_config();
		$notify_url=$this->_config->getNotifyUrl();
		$return_url=$this->_config->getReturnUrl();
		$mode=$this->_config->getMode();//"sandbox",'live'
		$currencyCode=$this->_config->getCurrencyCode();//"USD"
		$payment_type=$this->_config->getPaymentType();//Sale
		
		$show_url=$pay_param->getShowUrl();
		$cancel_url=$pay_param->getCancelUrl();
		$out_trade_no=$pay_param->getSn();
		$total_fee=$pay_param->getPayMoney($this->_config->getCurrency());
		$subject=$pay_param->getTitle();
		$body=$pay_param->getBody();

		// details about payment
		$paymentDetails = new PaymentDetailsType();
		
		$itemDetails = new PaymentDetailsItemType();
		$itemDetails->Name = $subject;
		$itemDetails->Description =$body;
		$itemDetails->ItemURL=$show_url;
		$itemDetails->Amount = $total_fee;
		$itemDetails->Quantity = 1;
		$itemDetails->ItemCategory = 'Physical';
		
		
		
		$paymentDetails->PaymentDetailsItem[0] = $itemDetails;
		$paymentDetails->TaxTotal = new BasicAmountType($currencyCode, 0);
		$paymentDetails->OrderTotal = new BasicAmountType($currencyCode, $total_fee);
		$paymentDetails->PaymentAction =$payment_type;
		$paymentDetails->InvoiceID=$out_trade_no;
		
		$setECReqDetails = new SetExpressCheckoutRequestDetailsType();
		$setECReqDetails->PaymentDetails[0] = $paymentDetails;
		$setECReqDetails->CancelURL = $cancel_url;
		$setECReqDetails->ReturnURL = $return_url;
		$setECReqDetails->NoShipping =1;
		$setECReqDetails->ReqConfirmShipping = 0;
		
		
		$setECReqType = new SetExpressCheckoutRequestType();
		$setECReqType->SetExpressCheckoutRequestDetails = $setECReqDetails;
		$setECReq = new SetExpressCheckoutReq();
		$setECReq->SetExpressCheckoutRequest = $setECReqType;
		
		
		$paypalService = new PayPalAPIInterfaceServiceService($config);
		try {
			$setECResponse = $paypalService->SetExpressCheckout($setECReq);
		} catch (\Exception $ex) {
			throw new Exception($ex->getMessage(),$ex->getCode(),$ex);
		}
		if($setECResponse->Ack =='Success') {
			$token = $setECResponse->Token;
			if ($mode=='sandbox') $payPalURL = 'https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&token=' . $token;
			else $payPalURL = 'https://www.paypal.com/webscr?cmd=_express-checkout&token=' . $token;
			return new PayRender(PayRender::OUT_URL, $payPalURL);
		}else{
			$err=array_shift($setECResponse->Errors);
			throw new Exception($err->LongMessage,$err->ErrorCode);
		}
	}
	public function payCallback(){
		$config=$this->_config();
		if (!isset($_REQUEST['token'])||!isset($_REQUEST['PayerID'])){
		    return (new \LSYS\PayGateway\Pay\PayResult\FailResult($_REQUEST,'sign is fail'))->setSignFail()->setParam($_REQUEST);
		}
		$token = $_REQUEST['token'];
		
		//查询订单数据
		$getExpressCheckoutDetailsRequest = new GetExpressCheckoutDetailsRequestType($token);
		$getExpressCheckoutReq = new GetExpressCheckoutDetailsReq();
		$getExpressCheckoutReq->GetExpressCheckoutDetailsRequest = $getExpressCheckoutDetailsRequest;
		$paypalService = new PayPalAPIInterfaceServiceService($config);
		try {
			$getECResponse = $paypalService->GetExpressCheckoutDetails($getExpressCheckoutReq);
		} catch (\Exception $ex) {
		    return (new \LSYS\PayGateway\Pay\PayResult\FailResult($ex->getTraceAsString(),$ex->getmessage()))->setLocalFail();
		}
		if ($getECResponse->Ack!='Success'){
			$err=array_shift($getECResponse->Errors);
			return (new \LSYS\PayGateway\Pay\PayResult\FailResult($getECResponse->Errors,$err->LongMessage));
		}
		
		$out_trade_no=$getECResponse->GetExpressCheckoutDetailsResponseDetails->InvoiceID;
		
		
		
		//从用户palpay账户进行扣款
		$currencyCode=$this->_config->getCurrencyCode();//"USD"
		
		$orderTotal = new BasicAmountType();
		$orderTotal->currencyID = $currencyCode;
		$orderTotal->value = $getECResponse->GetExpressCheckoutDetailsResponseDetails->PaymentDetails[0]->OrderTotal->value;;
		
		//扣款详细
		$paymentDetails= new PaymentDetailsType();
		$paymentDetails->OrderTotal = $orderTotal;
		$notify_url=$this->_config->getNotifyUrl();
		$paymentDetails->NotifyURL = $notify_url;
		
		
		$payerId=$getECResponse->GetExpressCheckoutDetailsResponseDetails->PayerInfo->PayerID;
		$payment_type=$this->_config->getPaymentType();//sale
		
		$DoECRequestDetails = new DoExpressCheckoutPaymentRequestDetailsType();
		$DoECRequestDetails->PayerID = $payerId;
		$DoECRequestDetails->Token = $token;
		$DoECRequestDetails->PaymentAction = $payment_type;
		$DoECRequestDetails->PaymentDetails[0] = $paymentDetails;
		
		$DoECRequest = new DoExpressCheckoutPaymentRequestType();
		$DoECRequest->DoExpressCheckoutPaymentRequestDetails = $DoECRequestDetails;
		
		
		$DoECReq = new DoExpressCheckoutPaymentReq();
		$DoECReq->DoExpressCheckoutPaymentRequest = $DoECRequest;
		
		try {
			/* wrap API method calls on the service object with a try catch */
			$DoECResponse = $paypalService->DoExpressCheckoutPayment($DoECReq);
		} catch (\Exception $ex) {
		    return (new \LSYS\PayGateway\Pay\PayResult\FailResult($ex->getTraceAsString(),$ex->getMessage()))->setLocalFail()->setParam($_REQUEST);
		}
		
		if ($DoECResponse->Ack!='Success'){
			$err=array_shift($DoECResponse->Errors);
			return (new \LSYS\PayGateway\Pay\PayResult\FailResult($DoECResponse->Errors,$err->LongMessage))->setParam($DoECResponse);
		}
		
		$out_trade_no=$getECResponse->GetExpressCheckoutDetailsResponseDetails->InvoiceID;
		$trade_no=$DoECResponse->DoExpressCheckoutPaymentResponseDetails->PaymentInfo[0]->TransactionID;
		$buyer_email=$getECResponse->GetExpressCheckoutDetailsResponseDetails->PayerInfo->Payer;
		$total_fee=$getECResponse->GetExpressCheckoutDetailsResponseDetails->PaymentDetails[0]->OrderTotal->value;
		$currency=$getECResponse->GetExpressCheckoutDetailsResponseDetails->PaymentDetails[0]->OrderTotal->currencyID;
		$result=new \LSYS\PayGateway\Pay\PayResult\SuccResult($_REQUEST,$out_trade_no,$trade_no);
		$result->setMoney(Money::factroy($total_fee,$this->_config->getCurrency($currency)))
		      ->setParam($DoECResponse)
			->setPayAccount($buyer_email)
		  ;
		return $result;
	}
}