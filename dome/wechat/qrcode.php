<?php
use LSYS\PayGateway\Pay\PayRender;
include __DIR__."/../Bootstarp.php";
PayRender::qrcodeRender('STSONG.TTF','扫描二维码进行付款','logo.png');