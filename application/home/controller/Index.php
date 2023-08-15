<?php

namespace app\home\controller;

use think\Controller;

class Index extends Controller
{
    public function index()
    {
        return $this->fetch();
    }

    /**
     * 注册
     */
    public function register()
    {
        return $this->fetch();
    }
    /**
     * 登录
     */
    public function login()
    {
        return $this->fetch();
    }
}
