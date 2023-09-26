<?php

namespace app\shop\controller\business;

use think\Controller;

class Base extends Controller
{
    // 用户模型
    protected $BusinessModel = null;

    public function __construct()
    {
        parent::__construct();

        // 加载模型
        $this->BusinessModel = model('business.Business');
    }

    public function register()
    {
        $mobile = $this->request->param('mobile','','trim');
        $password = $this->request->param('password','','trim');

        // 判断密码是否为空
        if(!$password)
        {
            $this->error('请输入密码');
        }

        // 生成密码盐
        $salt = build_ranstr();

        $password = md5($password.$salt);

        // 封装数据
        $data = [
            'mobile' => $mobile,
            'password' => $password,
            'salt' => $salt,
            'money' => 0,
            'auth' => 0,
            'deal' => 0,
        ];

        $source = model('business.Source')->where(['name' => ['LIKE','%商城%']])->find();

        if($source)
        {
            $data['sourceid'] = $source['id'];
        }

        $result = $this->BusinessModel->validate('common/business/Business.register')->save($data);

        if($result === false)
        {
            $this->error($this->BusinessModel->getError());
        }else{
            $this->success('注册成功');
        }
    }
}
