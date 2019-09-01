<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Adapter\Alipay;
use LSYS\PayGateway\Transfers\TransfersParam;
use LSYS\PayGateway\Transfers\TransfersAdapter\Batch;
use LSYS\PayGateway\Transfers\TransfersResult\BatchResult;
class Transfers extends  TransfersNotifyBatch implements Batch,\LSYS\PayGateway\Transfers\TransfersNotify{
	/**
	 * @var TransfersConfig
	 */
	protected $_config;
	/**
	 * @var TransfersParam[]
	 */
	protected $_items=array();
	public function __construct(TransfersConfig $config){
		$this->_config=$config;
		$this->_config->setMd5();
		
	}
	public function add(TransfersParam $param){
		$this->_items[]=$param;
		return $this;
	}
	public function render(){
		$alipay_config=$this->_config->asArray();
		$batch_fee=.0;
		$detail_data=array();
		foreach ($this->_items as $v){
			$batch_fee+=$v->getPayMoney();
			$item=array();
			array_push($item,$v->getTransfersNo());
			array_push($item,str_replace("^","",$v->getPayAccount()));
			array_push($item,str_replace("^","",$v->getPayName()));
			array_push($item,$v->getPayMoney());
			$msg=str_replace(array("^",'|','&','$','#',"\n","\r","\t"," ",'%','`','(',')','-','+','\\','*'), "", $v->getPayMsg());
			array_push($item,strip_tags($msg));
			array_push($detail_data,implode("^",$item));
		}
		
		//流水号1^收款方帐号1^真实姓名^付款金额1^备注说明1|流水号2^收款方帐号2^真实姓名^付款金额2^备注说明2
		$detail_data=implode("|",$detail_data);
		
		$notify_url=$this->_config->getNotifyUrl();
		
		$batch_fee=round($batch_fee,2);
		
		//付款账号
		$email = $this->_config->getSellerId();
		//必填
		
		//付款账户名
		$account_name = $this->_config->getSellerName();
		//必填，个人支付宝账号是真实姓名公司支付宝账号是公司名称
		
		//付款当天日期
		$pay_date =date("Ymd");
		//必填，格式：年[4位]月[2位]日[2位]，如：20100801
		
		$batch_no = date("YmdHis").rand(1000, 9999);
		//批次号
		// 		$batch_no = $_POST['WIDbatch_no'];
		
		//必填，格式：当天日期[8位]+序列号[3至16位]，如：201008010000001
		
		//付款总金额
		// 		$batch_fee = $_POST['WIDbatch_fee'];
		//必填，即参数detail_data的值中所有金额的总和
		
		//付款笔数
		// 		$batch_num = $_POST['WIDbatch_num'];
		$batch_num = count($this->_items);
		//必填，即参数detail_data的值中，“|”字符出现的数量加1，最大支持1000笔（即“|”字符出现的数量999个）
		
		//付款详细数据
		// 		$detail_data = $_POST['WIDdetail_data'];
		//必填，格式：流水号1^收款方帐号1^真实姓名^付款金额1^备注说明1|流水号2^收款方帐号2^真实姓名^付款金额2^备注说明2....
		
		/************************************************************/
		
		//构造要请求的参数数组，无需改动
		$parameter = array(
				"service" => "batch_trans_notify",
				"partner" => trim($alipay_config['partner']),
				"notify_url"	=> $notify_url,
				"email"	=> $email,
				"account_name"	=> $account_name,
				"pay_date"	=> $pay_date,
				"batch_no"	=> $batch_no,
				"batch_fee"	=> $batch_fee,
				"batch_num"	=> $batch_num,
				"detail_data"	=> $detail_data,
				"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
		);
		//建立请求
		require_once (__DIR__."/../../../../../libs/alipay_batch_trans/lib/alipay_submit.class.php");
		$alipaySubmit = new \AlipaySubmit($alipay_config);
		$html_text = $alipaySubmit->buildRequestForm($parameter,"get", "");
		return $html_text;
	}
	public function transfersNotify(){
		$alipay_config=$this->_config->asArray();
		//计算得出通知验证结果
		require_once (__DIR__."/../../../../../libs/alipay_batch_trans/lib/alipay_notify.class.php");
		$alipayNotify = new \AlipayNotify($alipay_config);
		
		if(!isset($_POST["sign"])||!$alipayNotify->verifyNotify()) {
		    return (new \LSYS\PayGateway\Transfers\TransfersResult\FailResult($_POST,'sign is fail'))->setSignFail()->setParam($_POST);
		}
		//Loger::instance(Loger::TYPE_TRANSFERS)->add($this->transfersName(),$_POST);
		//请在这里加上商户的业务逻辑程序代
		//——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
		//获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
		//批量付款数据中转账成功的详细信息
	   $results=[];
		//0315001^gonglei1@handsome.com.cn^龚本林^20.00^S^null^200810248427067^20081024143652|
		$success_details = @$_POST['success_details'];
		if(!empty($success_details )){
			$items=explode("|",$success_details);
			foreach ($items as $v){
				if(empty($v))continue;
				$_v=explode("^",$v);
				list($id,$account,$name,$money,$status,$msg,$pay_no,$time)=$_v;
				$result=(new \LSYS\PayGateway\Transfers\TransfersResult\SuccResult($v,$id, $pay_no))->setParam($_v);
				$results[]=$result;
			}
		}
		//批量付款数据中转账失败的详细信息
		//0315006^xinjie_xj@163.com^星辰公司1^20.00^F^TXN_RESULT_TRANSFER_OUT_CAN_NOT_EQUAL_IN^200810248427065^20081024143651|
		$fail_details = @$_POST['fail_details'];
		if(!empty($fail_details )){
			$items=explode("|",$fail_details);
			foreach ($items as $v){
				if(empty($v))continue;
				$_v=explode("^",$v);
				list($id,$account,$name,$money,$status,$msg,$pay_no,$time)=$_v;
				$result=(new \LSYS\PayGateway\Transfers\TransfersResult\FailResult($v,$msg,$id, $pay_no))->setParam($_v)->setLocalRollback();
				$results[]=$result;
			}
		}
		//判断是否在商户网站中已经做过了这次通知返回的处理
		//如果没有做过处理，那么执行商户的业务程序
		//如果有做过处理，那么不执行商户的业务程序
    	
		
		return new BatchResult($_POST, $results);
		
	
		//调试用，写文本函数记录程序运行情况是否正常
		//logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
	
		//——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	}
	public function transfersNotifyOutput($status=true,$msg=null){
		if ($status){
			//请不要修改或删除
			http_response_code(200);
			die( "success");
		}
		else{
			if (empty($msg))$msg='fail';
			die($msg);		//请不要修改或删除
		}
	}
}
