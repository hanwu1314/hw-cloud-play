<?php
namespace app\common\validate\Subject;

use think\Validate;

class Comment extends Validate
{
    protected $rule =   [
        'subid'  => 'require',
        'busid'   => 'require',
        'content' => 'require',
    ];

    protected $message  =   [
        'subid.require' => '课程必须填写',
        'busid.require'     => '用户必须填写',
        'content.require'   => '评价必须填写',
    ];
}