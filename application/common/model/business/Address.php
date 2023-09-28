<?php

namespace app\common\model\business;

use think\Model;
// 引入软删除模型
use traits\model\SoftDelete;

class Address extends Model
{
    // 使用软删除模型
    use SoftDelete;

    protected $name = 'business_address';

    // 开启自动写入时间
    protected $autoWriteTimestamp = true;

    // 设置字段的名字
    protected $createTime = false;

    // 禁止写入的时间字段
    protected $updateTime = false;

    // 软删除字段
    protected $deleteTime = 'deletetime';
}
