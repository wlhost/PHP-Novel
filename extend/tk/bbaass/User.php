<?php
namespace tk\bbaass;
use think\facade\Cookie;
use think\facade\Session;
use think\Db;
class User {
	public static $isLogin=False;
	public static $Username;
	public static $Email;
	public static $Time;
	public static $Id;
	public static $Group=0;
    public static $ResisterGroup=1;
	public static $GroupName=["游客","普通用户","未激活"];
	public static $UserData=[];
	public static $PasswordHash;
	public static $Cookie;
	public static function login($Username,$Password,$FACKPWD=false) {
		if (empty($Password)||empty($Username)) {
			return ["code"=>400,"errmsg"=>"提供参数不全"];
		}
		if(($Data=Db::table('user')->whereOr('Username',$Username)->whereOr('Email',$Username)->find())!=null) {
			if(password_verify($Password,$Data["PasswordHash"])||$FACKPWD) {
				if(!$FACKPWD) {
                    Cookie::forever('User_Name',$Username);
                    Cookie::forever('User_Token',password_hash($Data["Cookie"],PASSWORD_DEFAULT));
				}
                self::$isLogin=True;
				self::$Id=$Data["Id"];
				self::$Username=$Data["Username"];
				self::$PasswordHash=$Data["PasswordHash"];
				self::$Email=$Data["Email"];
				self::$Time=$Data["Time"];
				self::$Cookie=$Data["Cookie"];
				self::$Group=$Data["Group"];
				self::$UserData=json_decode($Data["Data"],true);
				return ["code"=>200];
			} else return ["code"=>403,"errmsg"=>"用户名或密码错误"];
		} else {
			return ["code"=>402,"errmsg"=>"用户不存在"];
		}
	}
	public static function register($Password,$Username,$Email) {
		if (empty($Password)||empty($Username)) {
			return ["code"=>400,"errmsg"=>"提供参数不全"];
		}
		if(Db::table('user')->whereOr('Username',$Username)->whereOr('Email',$Email)->find()==null) {
			$Id=Db::table('user')->insertGetId(($Data=[
				"Username"=>$Username,
				"PasswordHash"=>password_hash($Password,PASSWORD_DEFAULT),
				"Email"=>strtolower(trim($Email)),
				"Cookie"=>sha1(self::rand_str(32)."|$Username|$Password|".self::rand_str(32)),
				"Time"=>time(),
				"Group"=>self::$ResisterGroup
			]));
			self::$isLogin=True;
			self::$Id=$Id;
			self::$Group=self::$ResisterGroup;
			self::$Username=$Username;
			self::$PasswordHash=$Data["PasswordHash"];
			self::$Email=$Data["Email"];
			self::$Time=$Data["Time"];
			self::$Cookie=$Data["Cookie"];
			return ["code"=>200];
		} else {
			return ["code"=>401,"errmsg"=>"用户已存在"];
		}
	}
	public static function init() {
		if(Cookie::has('User_Name')&&Cookie::has('User_Name')) {
			$Username=Cookie::get('User_Name');
			$Token=Cookie::get('User_Token');
			if(($Data=Db::table('user')->where('Username',$Username)->find())!=null) {
				if(password_verify($Data["Cookie"],$Token)) {
					self::$isLogin=True;
					self::$Id=$Data["Id"];
					self::$Username=$Data["Username"];
					self::$PasswordHash=$Data["PasswordHash"];
					self::$Email=$Data["Email"];
					self::$Time=$Data["Time"];
					self::$Cookie=$Data["Cookie"];
					self::$Group=$Data["Group"];
					self::$UserData=json_decode($Data["Data"],true);
					return true;
				} else return false;
			}
		} else {
			return false;
		}
	}
	public static function updateData() {
		if(!self::$isLogin) return false;
        return Db::table("user")->where("Id",self::$Id)->update([
			"PasswordHash"=>self::$PasswordHash,
			"Email"=>self::$Email,
			"Cookie"=>self::$Cookie,
            "Data"=>json_encode(self::$UserData)
        ]);
	}
    public static function updateUserData($Id,Array $Array) {
		return Db::table("user")->where("Id",$Id)->update($Array);
	}
    public static function GetAllUserData(){
        $Data=Db::table("user")->select();
        for($i=0;$i<Count($Data);$i++) {
            $Data[$i]["Data"]=json_decode($Data[$i]["Data"],true);
        }
        return $Data;
    }
    public static function GetUserData($Id){
        if($Data=Db::table("user")->where("Id",$Id)->find()!=null) {
            $Data["Data"]=json_decode($Data["Data"],true);
        }
        return $Data;
    }
    public static function rand_User($Count=1) {
        return Db::table("user")->orderRaw("rand()")->limit($Count)->select();
    }
    public static function count() {
        return Db::table("user")->count();
    }
	public static function rand_str($length=8,$BigChar = true){
		$chars = 'abcdefghijklmnopqrstuvwxyz0123456789'.($BigChar ? 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' : '');
		$hash = '';
		for ($i=0;$i<$length;$i++){
			$hash .= $chars[mt_rand(0,strlen($chars)- 1)];
		}
		return $hash;
	}
}
User::init();