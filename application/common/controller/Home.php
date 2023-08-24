<?php

namespace app\common\controller;

use think\Controller;
use think\Request;

class Home extends Controller
{
    /** 不需要登录的方法 */
    protected $noNeedLogin = [];
    /** 全局登录的信息 */
    protected $LoginBusiness = [];

    public function __construct()
    {
        parent::__construct();
        // 获取当前的操作名
        $action = $this->request->action();

        // 判断哪些操作名需要调用验证登录
        if (!in_array($action, $this->noNeedLogin) && !in_array('*', $this->noNeedLogin)) {
            $this->isLogin();
        }
    }

    /**
     * 验证登录
     */
    protected function isLogin()
    {
        // 获取cookie
        $LoginBusiness = cookie('LoginBusiness') ?? [];
        if (empty($LoginBusiness)) {
            $this->error('请先登录', url('/home/index/login'));
        }

        // 从登录信息获取相应的数据查询数据表有没有这个用户
        $id = $LoginBusiness['id'] ?? 0;
        $mobile = $LoginBusiness['mobile'] ?? '';

        // 查询数据表
        $business = model('business.Business')
            ->where(['id' => $id, 'mobile' => $mobile])
            ->find();

        if (!$business) {
            // 清除非法cookie
            cookie('LoginBusiness', null);

            $this->error('非法登录', url('/home/index/login'));
        }

        // 查询出来的用户信息赋值全局使用
        $this->LoginBusiness = $business;

        // 赋值全局的视图
        $this->assign([
            'LoginBusiness' => $business
        ]);
    }
}
