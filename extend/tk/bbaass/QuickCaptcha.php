<?php
namespace tk\bbaass;
use think\facade\Cookie;
use cn\geetest\GeetestLib;
use think\facade\Session;
class QuickCaptcha {
	public static function captcha(){
		$GtSdk = new GeetestLib(GEETEST_CAPTCHA_ID, GEETEST_PRIVATE_KEY);
		$data = array(
			"user_id" => sha1(self::rand_str(64)),
			"client_type" => self::isMobile() ? "h5" : "web",
			"ip_address" => $_SERVER['REMOTE_ADDR']
		);
		$status=$GtSdk->pre_process($data,1);
		Session::set('gtserver',$status);
		Session::set('user_id'.(isset($_SERVER["HTTP_REFERER"]) ? substr("_".sha1($_SERVER["HTTP_REFERER"]),-8) : ""),$data['user_id']);
		return $GtSdk->get_response_str();
	}
	public static function checkCaptcha(){
		if(!isset($_POST['geetest_challenge'],$_POST['geetest_validate'],$_POST['geetest_seccode'])){
			return false;
		}
		$GtSdk = new GeetestLib(GEETEST_CAPTCHA_ID, GEETEST_PRIVATE_KEY);
		$data = array(
			"user_id" => Session::get('user_id'.(isset($_SERVER["HTTP_REFERER"]) ? substr("_".sha1($_SERVER["HTTP_REFERER"]),-8) : "")),
			"client_type" => self::isMobile() ? "h5" : "web",
			"ip_address" => $_SERVER['REMOTE_ADDR']
		);
		if(Session::get('gtserver')==1) {
			$result = $GtSdk->success_validate($_POST['geetest_challenge'], $_POST['geetest_validate'], $_POST['geetest_seccode'], $data);
			if ($result) {
				return true;
			} else {
				return false;
			}
		} else {
			if ($GtSdk->fail_validate($_POST['geetest_challenge'],$_POST['geetest_validate'],$_POST['geetest_seccode'])) {
				return true;
			} else {
				return false;
			}
		}
	}
	private static function rand_str($length=8){
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$password = '';
		for ($i=0;$i<$length;$i++){
			$password .= $chars[mt_rand(0,strlen($chars)- 1)];
		}
		return $password;
	}
	private static function isMobile() {
		// 如果有HTTP_X_WAP_PROFILE则一定是移动设备
		if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
			return true;
		} 
		// 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
		if (isset($_SERVER['HTTP_VIA'])) { 
			// 找不到为flase,否则为true
			return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
		} 
		// 脑残法，判断手机发送的客户端标志,兼容性有待提高。其中'MicroMessenger'是电脑微信
		if (isset($_SERVER['HTTP_USER_AGENT'])) {
			$clientkeywords = array('nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile','MicroMessenger'); 
			// 从HTTP_USER_AGENT中查找手机浏览器的关键字
			if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
				return true;
			} 
		} 
		// 协议法，因为有可能不准确，放到最后判断
		if (isset ($_SERVER['HTTP_ACCEPT'])) { 
			// 如果只支持wml并且不支持html那一定是移动设备
			// 如果支持wml和html但是wml在html之前则是移动设备
			if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
				return true;
			} 
		} 
		return false;
	}
}