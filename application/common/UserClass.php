<?php
namespace app\common;
use app\common\model\User;
use app\common\model\Config;
use app\common\model;
class UserClass
{
    public static function init()
    {
        if (Cookie::has('User_Name') && Cookie::has('User_Name')) {
            $Username = Cookie::get('User_Name');
            $Token = Cookie::get('User_Token');
            if (($Data = User::where('Username', $Username)->find()) != null) {
                if (password_verify($Data["Cookie"], $Token)) {
                    return $Data;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }
    public static function login($Username,$Password,$SkipPassword=false) {
		if (empty($Password)||empty($Username)) {
			return ["code"=>400,"errmsg"=>"提供参数不全"];
		}
		if(($Data=User::whereOr('Username',$Username)->whereOr('Email',$Username)->find())!=null) {
			if(password_verify($Password,$Data["PasswordHash"])||$SkipPassword) {
				return $Data;
			}
        }
        return ["code"=>403,"errmsg"=>"用户名或密码错误"];
    }
    public static function register($Password,$Username,$Email) {
		if (empty($Password)||empty($Username)) {
			return ["code"=>400,"errmsg"=>"提供参数不全"];
        }
        $User=new User();
		if(User::whereOr('Username',$Username)->whereOr('Email',$Email)->find()==null) {
			return User::create([
				"Username"=>$Username,
				"PasswordHash"=>password_hash($Password,PASSWORD_DEFAULT),
				"Email"=>strtolower(trim($Email)),
				"Cookie"=>sha1(self::rand_str(32)."|$Username|$Password|".self::rand_str(32)),
				"Time"=>time(),
				"Group"=>Config::get('DefaultGroup')->Data
			]);
		} else {
			return ["code"=>401,"errmsg"=>"用户已存在"];
		}
	}
    public static function rand_str($length = 8, $chars = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ')
    {
        $hash = '';
        for ($i = 0; $i < $length; $i++) {
            $hash .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $hash;
    }
}
