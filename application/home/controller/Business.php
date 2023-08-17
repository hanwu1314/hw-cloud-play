<?php

namespace app\home\controller;

use app\common\controller\Home;

class Business extends Home
{

    /** 用户模型 */
    protected $BusinessModel = null;

    public function __construct()
    {
        parent::__construct();
        $this->BusinessModel = model('business.Business');
    }

    public function index()
    {
        return $this->fetch();
    }
    /**
     * 修改资料
     */
    public function profile()
    {
        return $this->fetch();
    }
}
