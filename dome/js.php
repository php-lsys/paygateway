<?php 
use LSYS\PayGateway\Pay\PayRender;
if(!isset($type))die();
?>
<?php if ($type==PayRender::OUT_QRCODE):?>
<script>
(function(){
	function succ(url){
		alert('支付成功');
		window.location.href=url;
	};
	function fail(status,msg){
		status&&alert(msg);
	};
	window.__PayGateway=window.__PayGateway||{};
	window.__PayGateway.succ=succ;
	window.__PayGateway.fail=fail;
})();
</script>
<?php elseif($type==PayRender::OUT_CREDITCARD):?>
<script>
(function(){
	function succ(url){
		alert('支付成功');
		window.location.href=url;
	};
	function fail(msg){
		alert(msg);
	};
	function tips(status){
		status?alert('加载中'):alert('加载完成');
	};
	window.__PayGateway=window.__PayGateway||{};
	window.__PayGateway.succ=succ;
	window.__PayGateway.fail=fail;
	window.__PayGateway.tips=tips;
})();
</script>
<?php endif;?>