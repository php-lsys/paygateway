<?php

require_once  __DIR__."/Bootstarp.php";
require_once  __DIR__."/transfers_public.php";

/**
 * @var \LSYS\PayGateway\TransfersMgr $mgr
 */
$mgr=LSYS\PayGateway\DI::get()->paygatewayTransfersmgr();
function toPay($name){
	echo "<a href='./transfers.php?name={$name}&amount=1'>提现1元</a>";
}

foreach ($mgr->findAll() as $v){
	echo "支付类型:";
	echo $v->getName();
	to_pay($v->getName());
	echo "<br>";
}


