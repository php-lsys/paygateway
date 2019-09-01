<?php
use LSYS\PayGateway\Loger\DI;

include_once __DIR__."/../vendor/autoload.php";
$gateway_loger=\LSYS\PayGateway\Loger\DI::get()->paygatewayLoger();
$gateway_loger->addStorage(new \LSYS\PayGateway\Loger\Stroage\File('./',true));
$gateway_loger->add("TEST",NEW \LSYS\PayGateway\Pay\PayResult\SuccResult('TEST111111111','TEST','TEST'));