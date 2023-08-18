<?php

namespace app\common\validate;

use think\Validate;

/**
 * 邮件验证码的验证器
 */
class Ems extends Validate
{
    protected $rule =   [
        'event'  => 'require',
        'email'   => 'require',
        'code' => 'require',
        'ip' => 'require',
    ];

    protected $message  =   [
        'event.require' => '事件必填',
        'email.require'     => '邮箱必须填写',
        'code.require'   => '验证码未知',
        'ip.require'   => 'ip地址未知',
    ];
}
