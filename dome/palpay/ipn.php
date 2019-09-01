<?php
use LSYS\PayGateway\Adapter\Palpay\IPN;


use LSYS\PayGateway\Adapter\Palpay\Config;
include __DIR__."/../Bootstarp.php";
$_config=include_once './cfg.php';
$ipn=new IPN(Config::arr($_config));
switch ($ipn->getType()){
	case IPN::TYPE_PAYCALLBACK:
		pay_notify($ipn,$ipn->payNotify());
		$ipn->payNotifyOutput(true);
	break;
	case IPN::TYPE_REFUND:
	    refund_callback($ipn,$ipn->refundNotify());
		$ipn->refundNotifyOutput(true);
	break;
	case IPN::TYPE_INVALID:
		$ipn->output(false);
	break;
	default:
		$ipn->output(true);
}