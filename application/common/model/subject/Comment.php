<?php

namespace app\common\model\subject;

use think\Model;

class Comment extends Model
{
    protected $name = 'subject_comment';

    // 关联查询方法
    public function business()
    {
        return $this->belongsTo('app\common\model\business\Business','busid','id',[],'LEFT')->setEagerlyType(0);
    }
}
