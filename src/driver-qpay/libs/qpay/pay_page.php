<script src="http://pub.idqqimg.com/qqmobile/qqapi.js?_bid=152"></script>
<script type="text/javascript">
<?php
//your code
// window.__PayGateway=window.__PayGateway||{};
// window.__PayGateway.succ=function(url){
// 	alert('支付成功');
// 	window.location.href=url;
// };
// window.__PayGateway.fail=function(msg){
// 	alert('支付失败:'+msg);
// }
//  手动触发支付 
// 	window.__PayGateway.qqpay.pay();
?>
(function(w){
	function lpay(param){
		this.tokenId=param.tokenId||'';
		this.return_url=param.return_url||null;
		this.cancel_url=param.cancel_url||null;
	}
	lpay.prototype.success=function(){
		if(w.__PayGateway.succ)w.__PayGateway.succ(this.return_url);
		else w.location.href=this.return_url;
	}
	lpay.prototype.cancel=function(){
		w.location.href=this.cancel_url;
	}
	lpay.prototype.fail=function(msg){
		if(w.__PayGateway.fail)w.__PayGateway.fail(msg);
		else alert(msg);
	}
	lpay.prototype._pay=function(is_cancel){
		var self=this;
		mqq.tenpay.pay({
		    tokenId: self.tokenId,
		    callback:function(result){
		    	if(result.resultCode==0){
					return self.success();
				}else if(result.resultCode==-1){
					return is_cancel&&self.cancel();
				}
		    	return self.fail(res.err_msg);
		    },
		    pubAcc: "<?php echo $pubAcc;?>",
		    pubAccHint: "<?php echo $pubAccHint;?>"
		});
	}
	lpay.prototype.pay=function(){
		this._pay(false);
	}
	w.__PayGateway=w.__PayGateway||{};
	w.__PayGateway.qqpay=lpay;
})(window);
//run...
(function(w){
	w.__PayGateway=w.__PayGateway||{};
	var param=w.__PayGateway.param||{
		auto:1
	};
	param.return_url='<?php echo $return_url?>';
	param.cancel_url='<?php echo $cancel_url?>';
	param.tokenId='<?php echo $tokenId?>';
	w.__PayGateway.qqpay = new w.__PayGateway.qqpay(param);
	if(param.auto) w.__PayGateway.qqpay._pay(true);
})(window);
</script>





