<?php

namespace app\common\model\subject;

use think\Model;

class Subject extends Model
{
    //模型对应的是哪张表
    protected $name = "subject";

    //指定一个自动设置的时间字段
    //开启自动写入
    protected $autoWriteTimestamp = true;

    //设置字段的名字
    protected $createTime = "createtime"; //插入的时候设置的字段名

    //禁止 写入的时间字段
    protected $updateTime = false;

    // 追加属性
    protected $append = [
        'likes_count',
        'thumbs_cdn', // 课程封面
        'createtime_text'
    ];

    public function getThumbsCdnAttr($value, $data)
    {
        // 获取网站域名
        $cdn = config('site.url');

        $thumbs = empty($data['thumbs']) ? '/assets/img/coures.jpg' : $data['thumbs'];
        return $cdn . $thumbs;
    }

    //获取器方法
    public function getLikesCountAttr($value, $data)
    {
        $likes = trim($data['likes']);
        //字符串转化为数组
        $likes = explode(',', $data['likes']);
        $likes = array_filter($likes);
        return count($likes);
    }

    public function getCreatetimeTextAttr($value,$data)
    {
        $createtime = $data['createtime'] ?? '';

        return date("Y-m-d",$createtime);
    }

    // 关联查询
    public function category()
    {
        return $this->belongsTo('app\common\model\subject\Category','cateid','id',[],'LEFT')->setEagerlyType(0);
    }

}
