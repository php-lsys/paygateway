<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Palpay;
use LSYS\PayGateway\Pay\PayParam;
use LSYS\PayGateway\Pay\PayRender;
use PayPal\CoreComponentTypes\BasicAmountType;
use PayPal\Service\PayPalAPIInterfaceServiceService;
use PayPal\EBLBaseComponents\PaymentDetailsType;
use PayPal\PayPalAPI\DoDirectPaymentRequestType;
use PayPal\PayPalAPI\DoDirectPaymentReq;
use PayPal\EBLBaseComponents\DoDirectPaymentRequestDetailsType;
use PayPal\EBLBaseComponents\CreditCardDetailsType;
use PayPal\EBLBaseComponents\AddressType;
use PayPal\EBLBaseComponents\PersonNameType;
use PayPal\EBLBaseComponents\PayerInfoType;

class DirectPay extends Palpay{
	protected $_session;
	public function __construct(DirectPayConfig $config,\LSYS\Session $session=null){
		$this->_config=$config;
		$this->_session=$session?$session:\LSYS\Session\DI::get()->session();
	}
	public static function supportType($type){
	    return $type&(self::TYPE_WAP|self::TYPE_PC|self::TYPE_ANDROID|self::TYPE_IOS|self::TYPE_WECHAT);
	}
	/**
	 * {@inheritDoc}

	 */
	public function payRender(PayParam $pay_param){
		$config=$this->_config();
		$pay_url=$this->_config->getPayUrl();
		$return_url=$this->_config->getReturnUrl();
		$pay_param=serialize($pay_param);
		$key=md5($pay_param);
		$skey=md5($key);
		$spay=$this->_session->get("__PAYGATEWAY_DIRECT_PAY__",[]);
		$spay[$skey]=$pay_param;
		if (count($spay)>3) array_shift($spay);
		$this->_session->set("__PAYGATEWAY_DIRECT_PAY__",$spay);
		return new PayRender(PayRender::OUT_CREDITCARD, array('key'=>$key,'pay_param'=>$pay_param,'pay_url'=>$pay_url,'return_url'=>$return_url));
	}
	/**
	 * @param string $key
	 * @param CreditCardDetailsType $cardDetails
	 * @param PersonNameType $personName
	 * @param AddressType $address
	 * @return \LSYS\PayGateway\Pay\PayResult
	 */
	public function directPay($key,CreditCardDetailsType $cardDetails,PersonNameType $personName,AddressType $address){
		$session=$this->_session->get('__PAYGATEWAY_DIRECT_PAY__',[]);
		$skey=md5($key);
		if(!isset($session[$skey])||
			!($pay_param=@unserialize($session[$skey]))||
			!$pay_param instanceof PayParam){
			return (new \LSYS\PayGateway\Pay\PayResult\FailResult(null,'Timeout, please refresh the page.'))->setLocalFail();
		}
		$config=$this->_config();
		if (empty($address->Name)) $address->Name = "$personName->FirstName $personName->LastName";
		$paymentDetails = new PaymentDetailsType();
		$paymentDetails->ShipToAddress = $address;
		
		$total_fee=$pay_param->getPayMoney($this->_config->getCurrency());
		$currencyCode=$this->_config->getCurrencyCode();//"USD"
		$notify_url=$this->_config->getNotifyUrl();
		$payment_type=$this->_config->getPaymentType();//sale
		/*
		 *  Total cost of the transaction to the buyer. If shipping cost and tax
		 charges are known, include them in this value. If not, this value
		 should be the current sub-total of the order.
		
		 If the transaction includes one or more one-time purchases, this field must be equal to
		 the sum of the purchases. Set this field to 0 if the transaction does
		 not include a one-time purchase such as when you set up a billing
		 agreement for a recurring payment that is not immediately charged.
		 When the field is set to 0, purchase-specific fields are ignored.
		
		 * `Currency Code` - You must set the currencyID attribute to one of the
		 3-character currency codes for any of the supported PayPal
		 currencies.
		 * `Amount`
		 */
		$paymentDetails->OrderTotal = new BasicAmountType($currencyCode, $total_fee);
		/*
		 * 		Your URL for receiving Instant Payment Notification (IPN) about this transaction. If you do not specify this value in the request, the notification URL from your Merchant Profile is used, if one exists.
		
		 */
		$paymentDetails->NotifyURL = $notify_url;
		
		
		//information about the payer
		$payer = new PayerInfoType();
		$payer->PayerName = $personName;
		$payer->Address = $address;
		if (empty($payer->PayerCountry)) $payer->PayerCountry = $address->Country;
			
		
		$cardDetails->CardOwner = $payer;
		
		
		$ddReqDetails = new DoDirectPaymentRequestDetailsType();
		$ddReqDetails->CreditCard = $cardDetails;
		$ddReqDetails->PaymentDetails = $paymentDetails;
		$ddReqDetails->PaymentAction = $payment_type;
		
		$doDirectPaymentReq = new DoDirectPaymentReq();
		$doDirectPaymentReq->DoDirectPaymentRequest = new DoDirectPaymentRequestType($ddReqDetails);
		/*
		 * 		 ## Creating service wrapper object
		 Creating service wrapper object to make API call and loading
		 Configuration::getAcctAndConfig() returns array that contains credential and config parameters
		 */
		$paypalService = new PayPalAPIInterfaceServiceService($config);
		try {
			/* wrap API method calls on the service object with a try catch */
			$doDirectPaymentResponse = $paypalService->DoDirectPayment($doDirectPaymentReq);
		} catch (\Exception $ex) {
		    return (new \LSYS\PayGateway\Pay\PayResult\FailResult($ex->getTraceAsString(),$ex->getMessage()))->setLocalFail();
		}
		if ($doDirectPaymentResponse->Ack!='Success'){
			$err=array_shift($doDirectPaymentResponse->Errors);
			return new \LSYS\PayGateway\Pay\PayResult\FailResult($doDirectPaymentResponse->Errors,$pay_param->getSn(),  $doDirectPaymentResponse->TransactionID,  $err->LongMessage);
		}
		
		
		
		unset($session[$skey]);
		$this->_session->set('__PAYGATEWAY_DIRECT_PAY__',$session);
		
		
		return new \LSYS\PayGateway\Pay\PayResult\SuccResult(null,$pay_param->getSn(), $doDirectPaymentResponse->TransactionID,$doDirectPaymentResponse);
	}
	/**
	 * run pay...
	 */
	public function directPayFromPost(){
		$keys=array(
			'creditCardNumber',
			'creditCardType',
			'expDateMonth',
			'expDateYear',
			'cvv2Number',
			'firstName',
			'lastName',
			'firstName',
			'address1',
			'address2',
			'city',
			'state',
			'zip',
			'country',
			'phone',
			'key',
		);
		foreach ($keys as $v){
			if (!isset($_POST[$v]))PayRender::creditCardOutput(false,'miss param');
		}
		$cardDetails = new CreditCardDetailsType();
		$cardDetails->CreditCardNumber =$_POST['creditCardNumber'];
		$cardDetails->CreditCardType =$_POST['creditCardType'];
		$cardDetails->ExpMonth =$_POST['expDateMonth'];
		$cardDetails->ExpYear = $_POST['expDateYear'];
		$cardDetails->CVV2 =  $_POST['cvv2Number'];
		$personName = new PersonNameType();
		$personName->FirstName =  $_POST['firstName'];
		$personName->LastName =$_POST['lastName'];
		$address = new AddressType();
		$address->Street1 =$_POST['address1'];
		$address->Street2 =$_POST['address2'];
		$address->CityName =$_POST['city'];
		$address->StateOrProvince = $_POST['state'];
		$address->PostalCode = $_POST['zip'];
		$address->Country = $_POST['country'];
		$address->Phone = $_POST['phone'];
		$key=$_POST['key'];
		$result=$this->directPay($key, $cardDetails, $personName, $address);
		if ($result instanceof \LSYS\PayGateway\Pay\PayResult\SuccResult){
			PayRender::creditCardOutput(true);
		}else{
			PayRender::creditCardOutput(false,$result->getMsg());
		}
	}
}
