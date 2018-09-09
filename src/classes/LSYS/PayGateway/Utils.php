<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway;
class Utils{
	/**
	 * 比较两个金额
 	 * @param string $money
	 * @param string $moeny1
	 * @return boolean
	 */
	public static function money_equal($money,$moeny1){
		return round(floatval($money),2)==round(floatval($moeny1),2);
	}
	/**
	 * 格式化金额
	 * @param float $money
	 * @return number
	 */
	public static function money_format($money){
		return round(floatval($money),2);
	}
	/**
	 * 获取客户端IP
	 * @return string
	 */
	public static function client_ip(){
		$ip=false;
		if(isset($_SERVER["HTTP_CLIENT_IP"])){
			$ip = $_SERVER["HTTP_CLIENT_IP"];
		} else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ips = explode (", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
			if ($ip) { array_unshift($ips, $ip); $ip = FALSE; }
			for ($i = 0; $i < count($ips); $i++) {
				if (!preg_match ("/^(10|172\.16|192\.168)\./", $ips[$i])) {
					$ip = $ips[$i];
					break;
				}
			}
		}
		$ip=$ip ? $ip : (isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:null);
		if ($ip=='::1') $ip="127.0.0.1";
		return $ip;
	}
	/**
	 * 创建一个支付ID
	 * @param string $prefix 前缀
	 * @return string
	 */
	public static function snno_create($prefix){
		return $prefix.date("ymdHis").rand(100, 999);
	}
	/**
	 * 计算可提款金额
	 * @param float $money　总金额
	 * @param float $fee　费率
	 * @param number $min_fee_money 最小手续费
	 * @param number $max_fee_money　最大手续费 ０时表示不存在最大交易手续费
	 * @return number　可提现金额
	 */
	public static function transfers_money($money,$fee,$min_fee_money=0,$max_fee_money=0){
	    $money=floatval($money);
	    if($fee<=0) return $money;
	    if($money<=$min_fee_money) return 0;
	    $min_money=$min_fee_money/$fee;
	    if($money<=$min_money) return $money-$min_fee_money>0?$money-$min_fee_money:0;
	    if($max_fee_money>0){
    	    $max_money=$max_fee_money/$fee;
    	    if($money>=$max_money) return $money-$max_fee_money;
	    }
	    $pay_fee=$money*$fee/($fee+1);
	    return $money-round($pay_fee,2);
	}
	/**
	 * 计算一个提款费率
	 * @param float $fee　手续费率
	 * @param float $money　付款金额
	 * @param float $min_fee_money　最小手续费
	 * @param float $max_fee_money 最大手续费　0为不限制最大
	 * @return float　需要手续费
	 */
	public static function transfers_fee($fee,$money,$min_fee_money=0,$max_fee_money=0){
	    $pay_fee=$money*$fee;
	    if($pay_fee<$min_fee_money) return $min_fee_money;
	    if ($max_fee_money>0&&$pay_fee>$max_fee_money) return $max_fee_money;
		return round($pay_fee,2);
	}
	/**
	 * 重定向地址
	 * @param string $url
	 */
	public static function redirect_url($url,$code=301){
		if ($code!=301)$code=302;
		if(empty($url))die("redirect url can't be null");
		$url=str_replace(array("\n","\r","\t"), " ", $url);
		if(!headers_sent()){
			header("HTTP/1.1 {$code} Moved Permanently");//这个是说明返回的是301
			header("Location:".$url);//这个是重定向后的网址
		}
		$url=strip_tags($url);
		$url=str_replace("'", "", $url);
		$url=str_replace('"', "", $url);
		echo <<<REDICECTDOC
	<html>
		<head>
		<title>redirect...</title>
		<meta http-equiv="refresh" content="0;url={$url}">
		</head>
		<script>
		window.location.href='{$url}';
		</script>
	<body>redirect...</body>
	</html>
REDICECTDOC;
		die();
	}
	/**
	 * 得到一个加密字符串
	 * @param string $string
	 * @param string $key
	 * @return string
	 */
	public static function encode_url($string,$key){
		$string=trim($string);
		$key=trim($key);
		return $string.'-'.md5($string.$key);
	}
	/**
	 * 解析一个加密字符串
	 * @param string $string
	 * @param string $key
	 * @return NULL|string
	 */
	public static function decode_url($string,$key){
		if(empty($string)) return null;
		$string=trim($string);
		$key=trim($key);
		$hash=substr($string,-32);
		$string=substr($string,0,strlen($string)-33);
		if (md5($string.$key)!=$hash) return null;
		return $string;
	}
	/**
	 * 检查指定KEY是否存在在指定数组
	 * @param array $config
	 * @param array $keys
	 * @throws \LSYS\PayGateway\Exception
	 */
	public static function check_keys(array $config,array $keys){
	    $keys=array_diff( $keys ,array_keys($config));
	    if (count($keys)>0)throw new \LSYS\PayGateway\Exception(strtr("miss key[:keys]",array(":keys"=>implode(",", $keys))));
	}
}
