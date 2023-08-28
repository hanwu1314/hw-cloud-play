<?php

namespace app\common\validate\subject;

use think\Validate;

class Category extends Validate
{
    protected $rule = [
        'name'  => ['require', 'unique:subject_category'],
        'weight'   => ['require'],
    ];

    protected $message  =   [
        'name.require'   => '课程分类名称必须填写',
        'name.unique'  => '课程分类名称已重复',
        'weight.require'  => '权重必须填写',
    ];
}
