<?php
namespace app\common\model;

use think\facade\Cookie;
use think\Model;

class User extends Model
{
    protected $pk = 'Id';
    protected $json = ['Data'];
    protected $autoWriteTimestamp = true;
    protected $createTime = 'Create_Time';
    protected $updateTime = false;
    public function getStatusAttr($value)
    {
        $status = [-1 => '未激活', 0 => '封禁', 1 => '普通用户', 2 => '一星用户', 20 => 'VIP', 10000 => "管理员"];
        return $status[$value];
    }
}
