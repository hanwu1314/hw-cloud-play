<?php

namespace app\home\controller;

use think\Controller;

class Index extends Controller
{
    /** 用户模型 */
    protected $BusinessModel = null;

    public function _initialize()
    {
        $this->BusinessModel = model('business.Business');
    }

    public function index()
    {
        return $this->fetch();
    }

    /** 
     * 注册
     */
    public function register()
    {
        if ($this->request->isPost()) {
            $mobile = $this->request->param('mobile', '', 'trim');
            $password = $this->request->param('password', '', 'trim');


            if (empty($password)) {
                $this->error('密码不能为空');
            }
            /** 生成密码盐 */
            $salt = build_ranstr();
            /** md5密码 */
            $password = md5($password . $salt);

            // 组装注册的数据
            $data = [
                'mobile' => $mobile,
                'password' => $password,
                'salt' => $salt,
                'money' => 0,
                'auth' => 0,
                'deal' => 0,
            ];

            // ? 查询用户来源
            $source = model('business.Source')->where(['name' => ['LIKE', '%云课堂%']])->find();
            if ($source) {
                $data['sourceid'] = $source['id'];
            }

            // halt($data);

            $result = $this->BusinessModel->validate('common/business/Business.register')->save($data);
            if ($result === false) {
                $this->error($this->BusinessModel->getError());
            } else {
                $this->success('注册成功', url('/home/index/login'));
            }
        }
        return $this->fetch();
    }
    /**
     * 登录
     */
    public function login()
    {
        if ($this->request->isPost()) {
            $mobile = $this->request->param('mobile', '', 'trim');
            $password = $this->request->param('password', '', 'trim');

            $business = $this->BusinessModel->where(['mobile' => $mobile])->find();

            if (!$business) {
                $this->error('用户不存在');
            }
            if (md5($password . $business['salt']) != $business['password']) {
                $this->error('密码错误');
            }

            $data = [
                'id' => $business['id'],
                'mobile' => $business['mobile']
            ];
            cookie('LoginBusiness', $data);

            $this->success('登录成功', url('home/business/index'));
        }
        return $this->fetch();
    }
}
