<?php

namespace app\common\validate\business;

use think\Validate;

class Address extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'consignee' => 'require', //必填
        'mobile' => 'require', //必填
        'province' => 'require', //必填
        'city' => 'require', //必填
        'address' => 'require', //必填
        'status' => 'number|in:0,1',  //给字段设置范围
        'busid' => 'require', //必填
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message = [
        'consignee.require' => '请输入收货人名称',
        'mobile.require' => '请输入手机号码',
        'province.require' => '请选择省份',
        'city.require' => '请选择城市',
        'address.require' => '请输入详细地址',
        'busid.require' => '用户信息未知',
    ];

    /**
     * 验证场景
    */
    protected $scene = [
        'edit' => ['consignee','mobile','province','city','address','status']
    ];
}