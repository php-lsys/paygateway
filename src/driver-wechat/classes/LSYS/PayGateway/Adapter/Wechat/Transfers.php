<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Wechat;
use LSYS\PayGateway\Transfers\TransfersParam;
use LSYS\PayGateway\Utils;
use LSYS\PayGateway\Transfers\TransfersAdapter\RealTime;
class Transfers implements RealTime{
	/**
	 * @var Config
	 */
	protected $_config;
	public function __construct(Config $config){
		$this->_config=$config;
	}
	public function realTransfers(TransfersParam $param){
		require_once (__DIR__."/../../../../../libs/wechat/lib/WxPay.Api.php");
		\WxPayApi::$config=$this->_config->getWxPayConfigObj();
		
		$openid=$param->getPayAccount();
		$no=$param->getTransfersNo();
		$name=$param->getPayName();
		$money=round($param->getPayMoney()*100);
		$desc=strip_tags($param->getPayMsg());
		
		$input=new \WxPayTransfers();
		$input->SetPartner_trade_no($no);
		$input->SetOpenid($openid);
		$input->SetRe_user_name($name);
		$input->SetAmount($money);
		$input->SetDesc($desc);
		$input->SetSpbill_create_ip(Utils::client_ip());
		$input->SetMch_appid(\WxPayApi::$config->APPID);
		
		
		try{
		    $result=\WxPayApi::transfers($input,8,$response);
		}catch (\WxPayException $e){
		    return (new \LSYS\PayGateway\Transfers\TransfersResult\FailResult($response,$e->getMessage(),$no));
		}
		if ($result["return_code"] != "SUCCESS"){
		    return (new \LSYS\PayGateway\Transfers\TransfersResult\FailResult($response,$result['return_msg'],$no, @$result['payment_no']))->setParam($result);
		}
		if($result["result_code"]!='SUCCESS'){
		    return (new \LSYS\PayGateway\Transfers\TransfersResult\FailResult($response,$result['err_code_des'],$no, @$result['payment_no']))->setParam($result)->setLocalRollback();
		}
		return (new \LSYS\PayGateway\Transfers\TransfersResult\SuccResult($response,$no, @$result['payment_no']))->setParam($result);
	}
}
