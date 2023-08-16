<?php

namespace app\common\model\business;

use think\Model;

class Business extends Model
{
    /** 指定的数据表 */
    protected $name = 'business';
    /** 开启自动写入时间戳 */
    protected $autoWriteTimestamp = true;
    /** 定义时间戳字段名 */
    protected $createTime = 'createtime';
    /** 更新时间 */
    protected $updateTime = false;
}
