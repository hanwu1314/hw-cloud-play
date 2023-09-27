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

    public function login()
    {
        $mobile = $this->request->param('mobile','','trim');
        $password = $this->request->param('password','','trim');

        $business = $this->BusinessModel->where(['mobile' => $mobile])->find();

        if(!$business)
        {
            $this->error('手机号未注册');
        }

        $password = md5($password . $business['salt']);

        if($password != $business['password'])
        {
            $this->error('密码错误');
        }

        // 封装返回用户信息
        $data = [
            'id' => $business['id'],
            'mobile' => $business['mobile'],
            'mobile_text' => $business['mobile_text'],
            'avatar' => $business['avatar'],
            'avatar_cdn' => $business['avatar_cdn'],
            'nickname' => $business['nickname'],
            'email' => $business['email'],
            'gender' => $business['gender'],
            'province' => $business['province'],
            'city' => $business['city'],
            'district' => $business['district'],
            'region_text' => $business['region_text'],
            'auth' => $business['auth'],
        ];

        $this->success('登录成功',null,$data);
    }

    public function check()
    {
        $id = $this->request->param('id',0,'trim');
        $mobile = $this->request->param('mobile','','trim');

        $business = $this->BusinessModel->where(['id' => $id,'mobile' => $mobile])->find();

        if(!$business)
        {
            $this->error('非法登录');
        }

        // 封装返回用户信息
        $data = [
            'id' => $business['id'],
            'mobile' => $business['mobile'],
            'mobile_text' => $business['mobile_text'],
            'avatar' => $business['avatar'],
            'avatar_cdn' => $business['avatar_cdn'],
            'nickname' => $business['nickname'],
            'email' => $business['email'],
            'gender' => $business['gender'],
            'province' => $business['province'],
            'city' => $business['city'],
            'district' => $business['district'],
            'region_text' => $business['region_text'],
            'auth' => $business['auth'],
        ];

        $this->success('验证成功',null,$data);

    }
}
