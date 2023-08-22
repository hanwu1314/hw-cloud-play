<?php

namespace app\common\model\subject;

use think\Model;

class Order extends Model
{
    //模型对应的是哪张表
    protected $name = "subject_order";

    //指定一个自动设置的时间字段
    //开启自动写入
    protected $autoWriteTimestamp = true;

    //设置字段的名字
    protected $createTime = "createtime"; //插入的时候设置的字段名

    //禁止 写入的时间字段
    protected $updateTime = false;
}
