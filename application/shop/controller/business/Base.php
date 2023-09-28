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

    public function profile()
    {
        $params = $this->request->param();

        $business = $this->BusinessModel->find($params['id']);

        if(!$business)
        {
            $this->error('用户不存在');
        }

        // 封装更新数据
        $data = [
            'id' => $params['id'],
            'nickname' => $params['nickname'],
            'email' => $params['email'],
            'gender' => $params['gender'],
        ];

        // 如果邮箱有更新的话就要重新验证字段重置为0
        if($params['email'] != $business['email'])
        {
            $data['auth'] = 0;
        }

        // 修改密码
        $password = $params['password'] ?? '';

        if($password)
        {
            $repass = md5($password . $business['salt']);

            if($repass == $business['password'])
            {
                $this->error('新密码不能与原密码一致');
            }

            $salt = build_ranstr();

            $password = md5($password . $salt);

            $data['password'] = $password;

            $data['salt'] = $salt;
        }

        // 地区
        if(!empty($params['code']))
        {
            // 通过地区码去获取ID路径
            $path = model('Region')->where(['code' => $params['code']])->value('parentpath');

            // 如果路径为空就提示
            if(empty($path))
            {
                $this->error('所选地区不存在');
            }

            // 转成数组
            $pathArr = explode(',',$path);

            // 赋值
            $data['province'] = $pathArr[0] ?? null;
            $data['city'] = $pathArr[1] ?? null;
            $data['district'] = $pathArr[2] ?? null;
        }

        // 头像上传
        if(isset($_FILES['avatar']) && $_FILES['avatar']['size'] > 0)
        {
            // 调用上传图片函数
            $res = build_upload('avatar');

            // 上传失败
            if($res['code'] === 0)
            {
                $this->error($res['msg']);
            }

            // 赋值
            $data['avatar'] = $res['data'];
        }

        $result = $this->BusinessModel->validate('common/business/Business.profile')->isUpdate(true)->save($data);

        if($result === false)
        {
            if(isset($data['avatar']) && $_FILES['avatar']['size'])
            {
                @is_file(ltrim($data['avatar'],'/')) && @unlink($data['avatar'],'/');
            }

            $this->error($this->BusinessModel->getError());
        }else{
            if(isset($data['avatar']) && $_FILES['avatar']['size'])
            {
                @is_file(ltrim($business['avatar'],'/')) && @unlink($business['avatar'],'/');
            }

            $this->success('更新资料成功');
        }
    }
}
