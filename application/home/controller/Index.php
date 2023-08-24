<?php

namespace app\home\controller;

use think\Controller;

class Index extends Controller
{
    /** 用户模型 */
    protected $BusinessModel = null;

    // 课程模型
    protected $SubjectModel = null;

    public function _initialize()
    {
        $this->BusinessModel = model('business.Business');
        $this->SubjectModel = model('subject.Subject');
    }

    public function index()
    {
        // 查询点赞数最多的数据作为精选课程数据
        $HotData = $this->SubjectModel->OrderRaw('LPAD(LOWER(likes),10,0) DESC')->limit(8)->select();

        // 查询最新的课程作为轮播图的数据
        $SubjectData = $this->SubjectModel->order('createtime DESC')->limit(5)->select();

        // 赋值给视图使用
        $this->assign([
            'HotData' => $HotData,
            'SubjectData' => $SubjectData
        ]);
        return $this->fetch();
    }

    /** 
     * 注册
     */
    public function register()
    {
        // 判断是否已经登录 登录后重定向到首页
        if ($this->isUserLoggedIn()) {
            $this->redirectToHome();
        }

        if ($this->request->isPost()) {
            $mobile = $this->request->param('mobile', '', 'trim');
            $password = $this->request->param('password', '', 'trim');

            // 验证参数
            if (empty($mobile) || empty($password)) {
                $this->error('手机号和密码不能为空');
            }

            // 生成密码盐和哈希密码
            $salt = build_ranstr();
            $hashedPassword = password_hash($password . $salt, PASSWORD_DEFAULT);

            // 组装注册的数据
            $data = [
                'mobile' => $mobile,
                'password' => $hashedPassword,
                'salt' => $salt,
                'money' => 0,
                'auth' => 0,
                'deal' => 0,
            ];

            //  查询用户来源
            $source = model('business.Source')->where(['name' => ['LIKE', '%云课堂%']])->find();
            if ($source) {
                $data['sourceid'] = $source['id'];
            }

            // 保存用户数据
            $result = $this->registerUser($data);
            if ($result === false) {
                $this->error($this->getRegisterError());
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
        // 判断是否已经登录 登录后重定向到首页
        if ($this->isUserLoggedIn()) {
            $this->redirectToHome();
        }

        if ($this->request->isPost()) {
            $mobile = $this->request->param('mobile', '', 'trim');
            $password = $this->request->param('password', '', 'trim');

            // 查询用户
            $user = $this->getUserByMobile($mobile);

            if (!$user) {
                $this->error('用户不存在');
            }
            // 验证密码
            if (!password_verify($password . $user['salt'], $user['password'])) {
                $this->error('密码错误');
            }

            // 设置登录信息
            $data = [
                'id' => $user['id'],
                'mobile' => $user['mobile']
            ];
            cookie('LoginBusiness', $data);

            $this->success('登录成功', url('home/business/index'));
        }

        return $this->fetch();
    }

    /**
     * 判断是否已经登录
     */
    private function isUserLoggedIn()
    {
        $LoginBusiness = cookie('LoginBusiness') ?? '';
        return !empty($LoginBusiness);
    }

    /**
     * 重定向到首页
     */
    private function redirectToHome()
    {
        $this->redirect(url('/home/business/index'));
    }

    /**
     * 注册用户
     */
    private  function registerUser($data)
    {
        return $this->BusinessModel->validate('common/business/Business.register')->save($data);
    }

    /**
     * 获取注册错误信息
     */
    private  function getRegisterError()
    {
        return $this->BusinessModel->getError();
    }

    /**
     * 根据手机号获取用户
     */
    private function getUserByMobile($mobile)
    {
        return $this->BusinessModel->where(['mobile' => $mobile])->find();
    }

    /**
     * 注销
     */
    public function logout()
    {
        if ($this->request->isAjax()) {
            cookie('LoginBusiness', null);
            $this->success('退出账号成功');
        }
    }
}
