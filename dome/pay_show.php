<?php
use LSYS\PayGateway\Pay\PayAdapter;

require_once  __DIR__."/Bootstarp.php";
require_once  __DIR__."/pay_public.php";
$mgr=LSYS\PayGateway\DI::get()->paygatewayPaymgr();

function toPay($name,$bank=null){
	echo " <a href='./pay.php?name={$name}&key={$bank}&amount=0.01'>pay click</a> ";
}

foreach ($mgr->findAll(PayAdapter::TYPE_WECHAT) as $v){
    $k=$v->getName();
    $keys=$v->payMoreKey();
    if (is_array($keys)){
        echo "items:<div>";
        foreach ($keys as $key){
            echo "item: ".$k."-".$key;
            to_pay($k,$key);
            echo "<br>";
        }
        echo "</div>";
    }else{
        echo "item:<div>";
        echo "".$k;
        to_pay($k);
        echo "</div>";
    }
}

